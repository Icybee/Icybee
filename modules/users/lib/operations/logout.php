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
 * Log the user out of the system.
 *
 * @property-read User $record The active record representing the user that was logged out. This
 * property is still available after the user was logged out, unlike the {@link $user} property of
 * the `$core` object.
 */
class LogoutOperation extends \ICanBoogie\Operation
{
	/**
	 * Returns the record of the user to logout.
	 *
	 * The current user is returned.
	 */
	protected function get_record()
	{
		global $core;

		return $core->user;
	}

	/**
	 * Adds the {@link CONTROL_RECORD} control.
	 */
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true
		)

		+ parent::get_controls();
	}

	/**
	 * Always returns `true`.
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	/**
	 * Logs out the user.
	 *
	 * The {@link logout()} method of the user is invoked to log the user out.
	 *
	 * The location of the response can be defined by the `continue` request parameter or the request referer, or '/'.
	 */
	protected function process()
	{
		$this->record->logout();

		$request = $this->request;
		$this->response->location = isset($request['continue']) ? $request['continue'] : ($request->referer ? $request->referer : '/');

		return true;
	}
}