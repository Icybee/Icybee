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
 * Log the user out of the system by removing its identifier form its session.
 */
class Logout extends Operation
{
	/**
	 * Validates the operation if the user is actually connected.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		/*
		if (!$core->user_id)
		{
			$errors[] = t('You are not connected.');

			return false;
		}
		*/

		return true;
	}

	/**
	 * Removes the user id form the session and set the location of the operation to the location
	 * defined by the request's `continue` parameter or the request's referer, or '/'.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		$request = $this->request;

		unset($core->session->users['user_id']);

		$this->response->location = isset($request['continue']) ? $request['continue'] : ($request->referer ? $request->referer : '/');

		return true;
	}
}