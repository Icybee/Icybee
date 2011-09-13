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
use ICanBoogie\Operation;

/**
 * Disconnects the user from the system by removing its identifier form its session.
 */
class Disconnect extends Operation
{
	/**
	 * Validates the operation if the user is actually connected.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */

	protected function validate()
	{
		global $core;

		if (!$core->user_id)
		{
			throw new Exception('You are not connected.');
		}

		return true;
	}

	/**
	 * Removes the user id form the session and set the location of the operation to the location
	 * defined by `$_GET[location]` or the HTTP referer, or '/'.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		unset($core->session->users['user_id']);

		$this->location = isset($_GET['location']) ? $_GET['location'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');

		return true;
	}
}