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
 * Unlocks login locked after multiple failed login attempts.
 *
 * - username (string) Username of the locked account.
 * - token (string) Token to unlock the account.
 * - continue (string)[optional] Destination of the operation successful process. Default to '/'.
 */
class UnlockLogin extends Operation
{
	protected function __get_record()
	{
		$username = $this->request['username'];

		return $this->module->model->where('username = ? OR email = ?', $username, $username)->one;
	}

	protected function validate()
	{
		global $core;

		$token = $this->request['token'];

		if (!$this->request['username'] || !$token)
		{
			return false;
		}

		$user = $this->record;

		if (!$user)
		{
			throw new HTTPException('Unknown user', array(), 404);
		}

		$config = $core->configs['user'];

		if (!$config || empty($config['unlock_login_salt']))
		{
			throw new Exception
			(
				'<em>unlock_login_salt</em> is empty in the <em>user</em> config, here is one generated randomly: %salt', array
				(
					'%salt' => Security::generate_token(64, 'wide')
				)
			);
		}

		if ($user->metas['login_unlock_token'] != base64_encode(Security::pbkdf2($token, $config['unlock_login_salt'])))
		{
			throw new HTTPException('Invalid token.', array());
		}

		return true;
	}

	protected function process()
	{
		global $core;

		$user = $this->record;

		$user->metas['login_unlock_token'] = null;
		$user->metas['login_unlock_time'] = null;
		$user->metas['failed_login_count'] = 0;

		wd_log_done('Login has been unlocked');

		$this->location = isset($this->request['continue']) ? $this->request['continue'] : '/';

		return true;
	}
}