<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Users;

use ICanBoogie\Exception;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;
use ICanBoogie\Security;

/**
 * The "nonce-login" operation is used to login a user using a one time, time limited pass created
 * by the "nonce-request" operation.
 */
class NonceLogin extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true
		)

		+ parent::__get_controls();
	}

	protected function __get_record()
	{
		global $core;

		return isset($this->params['email']) ? $core->models['users']->find_by_email($this->params['email'])->one : null;
	}

	protected function validate()
	{
		global $core;

		$params = $this->params;

		if (empty($params['token']))
		{
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

		$token = $params['token'];

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

		$this->location = $user->url('profile');

		return true;
	}
}