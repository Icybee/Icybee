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
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;
use ICanBoogie\Security;

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

		return $this->request['email'] ? $core->models['users']->filter_by_email($this->request['email'])->one : null;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$request = $this->request;
		$token = $request['token'];

		if (!$token)
		{
			$errors['token'] = t('Token is required.');

			return false;
		}

		$user = $this->record;

		$now = time();
		$expires = $user->metas['nonce_login.expires'];

		if ($expires < $now)
		{
			throw new HTTPException('This nonce login has expired.');
		}

		$config = $core->configs['user'];

		if (!$config || empty($config['nonce_login_salt']))
		{
			throw new Exception
			(
				'<em>nonce_login_salt</em> is empty in the <em>user</em> config, here is one generated randomly: %salt', array
				(
					'%salt' => Security::generate_token(64, 'wide')
				)
			);
		}

		if ($user->metas['nonce_login.token'] != base64_encode(Security::pbkdf2($token, $config['nonce_login_salt'])))
		{
			throw new HTTPException('Invalid token');
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		if ($ip != $user->metas['nonce_login.ip'])
		{
			throw new HTTPException('Invalid remote address');
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

		$this->response->location = $user->url('profile');

		return true;
	}
}