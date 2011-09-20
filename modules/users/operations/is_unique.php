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

use ICanBoogie\ActiveRecord\Users;
use ICanBoogie\Operation;

/**
 * Checks whether the specified email or username is unique, as in _not already used_.
 */
class IsInique extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		$request = $this->request;

		if (!$request[User::USERNAME] && !$request[User::EMAIL])
		{
			$this->errors[] = t('Missing %username or %email', array('%username' => User::USERNAME, '%email' => User::EMAIL));
			$this->errors[User::USERNAME] = true;
			$this->errors[User::EMAIL] = true;

			return false;
		}

		return true;
	}

	protected function process()
	{
		$request = $this->request;

		$uid = $request[User::UID] ?: 0;

		$is_unique_username = true;
		$is_unique_email = true;

		if ($request[User::USERNAME])
		{
			$is_unique_username = !$this->module->model->select('uid')->where('username = ? AND uid != ?', $request[User::USERNAME], $uid)->rc;
		}

		if ($request[User::EMAIL])
		{
			$is_unique_email = !$this->module->model->select('uid')->where('email = ? AND uid != ?', $request[User::EMAIL], $uid)->rc;
		}

		$this->response->username = $is_unique_username;
		$this->response->email = $is_unique_email;

		if (!$is_unique_email)
		{
			wd_log_error('This email address is already used');
		}

		if (!$is_unique_username)
		{
			wd_log_error('This username is already used');
		}

		return $is_unique_email && $is_unique_username;
	}
}