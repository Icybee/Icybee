<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes;

use ICanBoogie\Operation;

class OfflineOperation extends Operation
{
	/**
	 * Controls for the operation: permission(maintain), record and ownership.
	 *
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_MAINTAIN,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	/**
	 * Changes the target record is_online property to false and updates the record.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		$record = $this->record;
		$record->is_online = false;
		$record->save();

		\ICanBoogie\log_success('!title is now offline', array('!title' => $record->title));

		return true;
	}
}