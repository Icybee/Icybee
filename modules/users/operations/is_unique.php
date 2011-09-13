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
		$params = $this->params;

		if (empty($params[User::USERNAME]) && empty($params[User::EMAIL]))
		{
			wd_log_error('Missing %username or %email', array('%username' => User::USERNAME, '%email' => User::EMAIL));

			return false;
		}

		return true;
	}

	protected function process()
	{
		$params = $this->params;

		$uid = isset($params[User::UID]) ? $params[User::UID] : 0;

		$is_unique_username = true;
		$is_unique_email = true;

		if (isset($params[User::USERNAME]))
		{
			$is_unique_username = !$this->module->model->select('uid')->where('username = ? AND uid != ?', $params[User::USERNAME], $uid)->rc;
		}

		if (isset($params[User::EMAIL]))
		{
			$is_unique_email = !$this->module->model->select('uid')->where('email = ? AND uid != ?', $params[User::EMAIL], $uid)->rc;
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