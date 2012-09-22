<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

/**
 * Includes a record is the home page.
 */
class HomeIncludeOperation extends \ICanBoogie\Operation
{
	/**
	 * Controls for the operation: permission(maintain), record and ownership.
	 *
	 * @see ICanBoogie.Operation::get_controls()
	 */
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_MAINTAIN,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::get_controls();
	}

	protected function validate(\ICanBoogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		$record = $this->record;
		$record->is_home_excluded = false;
		$record->save();

		$this->response->message = array('%title is now included in the home page', array('%title' => $record->title));

		return true;
	}
}