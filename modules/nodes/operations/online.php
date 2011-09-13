<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Nodes;

use ICanBoogie\Module;
use ICanBoogie\Operation;

class Online extends Operation
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

	protected function validate()
	{
		return true;
	}

	/**
	 * Changes the target record is_online property to true and updates the record.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		$record = $this->record;
		$record->is_online = true;
		$record->save();

		wd_log_done('!title is now online', array('!title' => $record->title));

		return true;
	}
}