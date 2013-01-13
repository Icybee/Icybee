<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use ICanBoogie\Mailer;
use ICanBoogie\I18n;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Exception;

class LoginOperation extends \ICanBoogie\Operation
{
	/**
	 * Adds form control.
	 *
	 * @see ICanBoogie.Operation::get_controls()
	 */
	protected function get_controls()
	{
		return array
		(
// 			self::CONTROL_SESSION_TOKEN => true,
			self::CONTROL_FORM => true
		)

		+ parent::get_controls();
	}

	/**
	 * Returns the "connect" form of the target module.
	 *
	 * @see ICanBoogie.Operation::get_form()
	 */
	protected function get_form()
	{
		return new LoginForm();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$request = $this->request;
		$username = $request[User::USERNAME];
		$password = $request[User::PASSWORD];

		$user = $core->models['users']->where('username = ? OR email = ?', $username, $username)->one;

		if (!$user)
		{
			$errors[User::PASSWORD] = new FormattedString('Unknown username/password combination.');

			return false;
		}

		$now = time();
		$login_unlock_time = $user->metas['login_unlock_time'];

		if ($login_unlock_time)
		{
			if ($login_unlock_time > $now)
			{
				throw new \ICanBoogie\HTTP\HTTPError
				(
					\ICanBoogie\format("The user account has been locked after multiple failed login attempts.
					An e-mail has been sent to unlock the account. Login attempts are locked until %time,
					unless you unlock the account using the email sent.", array
					(
						'%count' => $user->metas['failed_login_count'],
						'%time' => I18n\format_date($login_unlock_time, 'HH:mm')
					)),

					403
				);
			}

			$user->metas['login_unlock_time'] = null;
		}

		if (!$user->compare_password($password))
		{
			$errors[User::PASSWORD] = new FormattedString('Unknown username/password combination.');

			$user->metas['failed_login_count'] += 1;
			$user->metas['failed_login_time'] = $now;

			if ($user->metas['failed_login_count'] > 10)
			{
				$config = $core->configs['user'];

				if (!$config || empty($config['unlock_login_salt']))
				{
					throw new Exception
					(
						'<q>unlock_login_salt</q> is empty in the <q>user</q> config, here is one generated randomly: %salt', array
						(
							'%salt' => \ICanBoogie\generate_token(64, 'wide')
						)
					);
				}

				$token = base64_encode(\ICanBoogie\generate_token(32, 'wide'));

				$user->metas['login_unlock_token'] = base64_encode(\ICanBoogie\pbkdf2($token, $config['unlock_login_salt']));
				$user->metas['login_unlock_time'] = $now + 3600;

				$until = I18n\format_date($now + 3600, 'HH:mm');

				$url = $core->site->url . '/api/users/unlock_login?' . http_build_query
				(
					array
					(
						'username' => $username,
						'token' => $token,
						'continue' => $request->uri
					)
				);

				$t = new Proxi(array('scope' => array(\ICanBoogie\normalize($user->constructor, '_'), 'connect', 'operation')));

				$mailer = new Mailer
				(
					array
					(
						Mailer::T_DESTINATION => $user->email,
						Mailer::T_FROM => 'no-reply@icybee.org', // FIXME-20110803: should be replaced by a configurable value
						Mailer::T_SUBJECT => "Your account has been locked",
						Mailer::T_MESSAGE => <<<EOT
You receive this message because your account has been locked.

After multiple failed login attempts your account has been locked until $until. You can use the
following link to unlock your account and try to login again:

$url

If you forgot your password, you'll be able to request a new one.

If you didn't try to login neither forgot your password, this message might be the result of an
attack attempt on the website. If you think this is the case, please contact its admin.

The remote address of the request was: $request->ip.
EOT
					)
				);

				$mailer();

				\ICanBoogie\log_error("Your account has been locked, a message has been sent to your e-mail address.");
			}

			return false;
		}

		if (!$user->is_admin && !$user->is_activated)
		{
			$this->response->errors[] = new FormattedString('User %username is not activated', array('%username' => $username));

			return false;
		}

		$this->record = $user;

		return true;
	}

	/**
	 * Saves the user id in the session, sets the `user` property of the core object, updates the
	 * user's last connection date and finaly changes the operation location to the same request
	 * uri.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		$user = $this->record;

		$user->login();

		$user->metas['failed_login_count'] = null;
		$user->metas['failed_login_time'] = null;

		$core->models['users']->execute
		(
			'UPDATE {self} SET lastconnection = now() WHERE uid = ?', array
			(
				$core->user_id
			)
		);

		$this->response->location = $this->request['continue'] ?: $this->request->uri;

		return true;
	}
}