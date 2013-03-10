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

use ICanBoogie\Exception;
use ICanBoogie\Operation;
use ICanBoogie\PermissionRequired;

/**
 * The "nonce-login" operation is used to login a user using a one time, time limited pass created
 * by the "nonce-request" operation.
 */
class NonceLoginOperation extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true
		)

		+ parent::get_controls();
	}

	protected function get_record()
	{
		global $core;

		$email = $this->request['email'];

		if (!$email)
		{
			return;
		}

		/* @var $user User */

		$user = $core->models['users']->filter_by_email($this->request['email'])->one;

		if (!$user)
		{
			return;
		}

		if ($user->constructor != 'users')
		{
			$user = $core->models[$user->constructor][$user->uid];
		}

		return $user;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$request = $this->request;
		$token = $request['token'];

		if (!$token)
		{
			$errors['token'] = 'Token is required.';

			return false;
		}

		$user = $this->record;

		$now = time();
		$expires = $user->metas['nonce_login.expires'];

		if ($expires < $now)
		{
			throw new PermissionRequired('This nonce login ticket has expired.');
		}

		$config = $core->configs['user'];

		if (!$config || empty($config['nonce_login_salt']))
		{
			throw new Exception
			(
				'<em>nonce_login_salt</em> is empty in the <em>user</em> config, here is one generated randomly: %salt', array
				(
					'%salt' => \ICanBoogie\generate_token(64, \ICanBoogie\TOKEN_WIDE)
				)
			);
		}

		if ($user->metas['nonce_login.token'] != base64_encode(\ICanBoogie\pbkdf2($token, $config['nonce_login_salt'])))
		{
			throw new PermissionRequired('Invalid nonce token.');
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		if ($ip != $user->metas['nonce_login.ip'])
		{
			throw new PermissionRequired("Remote address don't match.");
		}

		return true;
	}

	protected function process()
	{
		$user = $this->record;

		$user->metas['nonce_login.expires'] = null;
		$user->metas['nonce_login.token'] = null;
		$user->metas['nonce_login.ip'] = null;

		$user->login();

		\ICanBoogie\log_info("You are now logged in, please enter your password.");

		$this->response->location = $user->url('profile');

		return true;
	}
}