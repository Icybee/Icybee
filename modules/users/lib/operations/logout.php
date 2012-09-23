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
 * Log the user out of the system by removing its identifier form its session.
 */
class LogoutOperation extends \ICanBoogie\Operation
{
	/**
	 * Validates the operation if the user is actually connected.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
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

		$core->user->logout();

		$request = $this->request;
		$this->response->location = isset($request['continue']) ? $request['continue'] : ($request->referer ? $request->referer : '/');

		return true;
	}
}