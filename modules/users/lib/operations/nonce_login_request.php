<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users;

use ICanBoogie\Exception;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Mailer;
use ICanBoogie\Operation;
use ICanBoogie\Security;

/**
 * Provides a nonce login.
 */
class NonceLoginRequestOperation extends Operation
{
	const FRESH_PERIOD = 3600;
	const COOLOFF_DELAY = 900;

	protected function __get_record()
	{
		global $core;

		return $core->models['users']->find_by_email($this->request['email'])->one;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		if (!$this->request['email'])
		{
			$errors['email'] = t('The field %field is required!', array('%field' => 'Votre adresse E-Mail'));

			return false;
		}

		$email = $this->request['email'];

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$errors['email'] = t('Invalid e-mail address: %email.', array('%email' => $email));

			return false;
		}

		$user = $this->record;

		if (!$user)
		{
			$errors['email'] = t('Unknown e-mail address.');

			return false;
		}

		$now = time();
		$expires = $user->metas['nonce_login.expires'];

		if ($expires && ($now + self::FRESH_PERIOD - $expires < self::COOLOFF_DELAY))
		{
			throw new HTTPException("A message has already been sent to your e-mail address. In order to reduce abuses, you won't be able to request a new one until :time.", array(':time' => wd_format_date($expires - self::FRESH_PERIOD + self::COOLOFF_DELAY, 'HH:mm')), 403);
		}

		return true;
	}

	protected function process()
	{
		global $core;

		$user = $this->record;

		$token = md5(Security::generate_token(32, 'wide'));
		$expires = time() + self::FRESH_PERIOD;
		$ip = $_SERVER['REMOTE_ADDR'];

		$config = $core->configs['user'];

		if (!$config || empty($config['nonce_login_salt']))
		{
			throw new Exception
			(
				'<q>nonce_login_salt</q> is empty in the <q>user</q> config, here is one generated randomly: %salt', array
				(
					'%salt' => Security::generate_token(64, 'wide')
				)
			);
		}

		$user->metas['nonce_login.token'] = base64_encode(Security::pbkdf2($token, $config['nonce_login_salt']));
		$user->metas['nonce_login.expires'] = $expires;
		$user->metas['nonce_login.ip'] = $ip;

		$url = $core->site->url . "/api/nonce-login/$user->email/$token";
		$until = wd_format_date($expires, 'HH:mm');

		$t = new Proxi(array('scope' => array(wd_normalize($user->constructor, '_'), 'nonce_login_request', 'operation')));

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

		$this->response->success = $t('success', array('%email' => $user->email));

		return true;
	}
}