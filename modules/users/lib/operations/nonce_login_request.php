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

use ICanBoogie\DateTime;
use ICanBoogie\I18n;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Mailer;
use ICanBoogie\Operation;
use ICanBoogie\PermissionRequired;

/**
 * Provides a nonce login.
 */
class NonceLoginRequestOperation extends Operation
{
	const FRESH_PERIOD = 3600;
	const COOLOFF_DELAY = 900;

	/**
	 * Returns the record assocaiated with the email address specified by the `email` param.
	 *
	 * @return User|null
	 */
	protected function get_record()
	{
		global $core;

		$email = $this->request['email'];

		if (!$email)
		{
			return;
		}

		/* @var $record User */

		$record = $core->models['users']->filter_by_email($email)->one;

		if ($record && $record->constructor != 'users')
		{
			$record = $core->models[$record->constructor][$record->uid];
		}

		return $record;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$email = $this->request['email'];

		if (!$email)
		{
			$errors['email'] = new FormattedString('The field %field is required!', array('%field' => 'Votre adresse E-Mail'));

			return false;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$errors['email'] = new FormattedString("Invalid email address: %email.", array('%email' => $email));

			return false;
		}

		$user = $this->record;

		if (!$user)
		{
			$errors['email'] = new FormattedString("Unknown email address.");

			return false;
		}

		$now = time();
		$expires = $user->metas['nonce_login.expires'];

		if ($expires && ($now + self::FRESH_PERIOD - $expires < self::COOLOFF_DELAY))
		{
			/*

			var_dump($now, new \ICanBoogie\DateTime(null, 'UTC'), new \DateTime('now'), new \ICanBoogie\DateTime($expires));

			exit;

			$expires_date = new \ICanBoogie\DateTime($expires - self::FRESH_PERIOD + self::COOLOFF_DELAY, new \DateTimeZone('UTC'));
			$expires_date->timezone = $core->timezone;

			throw new PermissionRequired
			(
				new FormattedString('nonce_login_request.already_sent', array
				(
					':time' => $expires_date->format('H:m')
				)),

				403
			);
			*/

			throw new PermissionRequired
			(
				new FormattedString("A message has already been sent to your e-mail address. In order to reduce abuses, you won't be able to request a new one until :time.", array
				(
					':time' => DateTime::from($expires - self::FRESH_PERIOD + self::COOLOFF_DELAY)->local->format('H:i')
				)),

				403
			);
		}

		return true;
	}

	protected function process()
	{
		global $core;

		$user = $this->record;

		$token = md5(\ICanBoogie\generate_token(32, \ICanBoogie\TOKEN_WIDE));
		$expires = time() + self::FRESH_PERIOD;
		$ip = $_SERVER['REMOTE_ADDR'];

		$config = $core->configs['user'];

		if (!$config || empty($config['nonce_login_salt']))
		{
			throw new \Exception(new I18n\FormattedString
			(
				'<q>nonce_login_salt</q> is empty in the <q>user</q> config, here is one generated randomly: %salt', array
				(
					'%salt' => \ICanBoogie\generate_token(64, \ICanBoogie\TOKEN_WIDE)
				)
			));
		}

		$user->metas['nonce_login.token'] = base64_encode(\ICanBoogie\pbkdf2($token, $config['nonce_login_salt']));
		$user->metas['nonce_login.expires'] = $expires;
		$user->metas['nonce_login.ip'] = $ip;

		$url = $core->site->url . "/api/nonce-login/$user->email/$token";
		$until = \ICanBoogie\I18n\format_date($expires, 'HH:mm');

		$t = new Proxi(array('scope' => array(\ICanBoogie\normalize($user->constructor, '_'), 'nonce_login_request', 'operation')));

		$mailer = new Mailer
		(
			array
			(
				Mailer::T_DESTINATION => $user->email,
				Mailer::T_FROM => $core->site->title . ' <no-reply@icybee.org>', // TODO-20110709: customize to site domain
				Mailer::T_SUBJECT => $t('message.subject'),
				Mailer::T_MESSAGE => $t
				(
					'message.template', array
					(
						':url' => $url,
						':until' => $until,
						':ip' => $ip
					)
				)
			)
		);

		$mailer();

		$this->response->message = $t('success', array('%email' => $user->email));

		return true;
	}
}