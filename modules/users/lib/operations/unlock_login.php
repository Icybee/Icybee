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

/**
 * Unlocks login locked after multiple failed login attempts.
 *
 * - username (string) Username of the locked account.
 * - token (string) Token to unlock the account.
 * - continue (string)[optional] Destination of the operation successful process. Default to '/'.
 */
class UnlockLoginOperation extends \ICanBoogie\Operation
{
	protected function get_record()
	{
		$username = $this->request['username'];

		return $this->module->model->where('username = ? OR email = ?', $username, $username)->one;
	}

	protected function validate(\ICanboogie\Errors $errors)
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
			throw new \Exception('Unknown user');
		}

		$config = $core->configs['user'];

		if (!$config || empty($config['unlock_login_salt']))
		{
			throw new \Exception(\ICanBoogie\format
			(
				'<em>unlock_login_salt</em> is empty in the <em>user</em> config, here is one generated randomly: %salt', array
				(
					'%salt' => \ICanBoogie\generate_token(64, 'wide')
				)
			));
		}

		if ($user->metas['login_unlock_token'] != base64_encode(\ICanBoogie\pbkdf2($token, $config['unlock_login_salt'])))
		{
			throw new \Exception('Invalid token.');
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

		$this->response->message = 'Login has been unlocked';
		$this->response->location = isset($this->request['continue']) ? $this->request['continue'] : '/';

		return true;
	}
}