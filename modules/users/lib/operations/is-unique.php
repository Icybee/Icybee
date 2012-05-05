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

use ICanBoogie\ActiveRecord\User;
use ICanBoogie\Operation;

/**
 * Checks whether the specified email or username is unique, as in _not already used_.
 */
class IsUniqueOperation extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$request = $this->request;
		$username = $request[User::USERNAME];
		$email = $request[User::EMAIL];
		$uid = $request[User::UID] ?: 0;

		/*
		if (!$username && !$email)
		{
			$errors[] = t('Missing %username or %email', array('%username' => User::USERNAME, '%email' => User::EMAIL));
			$errors[User::USERNAME] = true;
			$errors[User::EMAIL] = true;

			return false;
		}
		*/

		if ($username)
		{
			if ($this->module->model->select('uid')->where('username = ? AND uid != ?', $username, $uid)->rc)
			{
				$errors[User::USERNAME] = 'This username is already used';
			}
		}
		else
		{
			$errors[User::USERNAME] = null;
		}

		if ($email)
		{
			if ($this->module->model->select('uid')->where('email = ? AND uid != ?', $email, $uid)->rc)
			{
				$errors[User::EMAIL] = 'This email is already used';
			}
		}
		else
		{
			$errors[User::EMAIL] = null;
		}

		return count($errors) == 0;
	}

	protected function process()
	{
		/*
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

		$this->response['username'] = $is_unique_username;
		$this->response['email'] = $is_unique_email;

		if (!$is_unique_email)
		{
			$this->response->errors['email'] = t('This email address is already used');
		}

		if (!$is_unique_username)
		{
			$this->response->errors['username'] = t('This username is already used');
		}

		return $is_unique_email && $is_unique_username;
		*/

		return true;
	}
}