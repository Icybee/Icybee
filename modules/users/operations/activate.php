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

use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * Enables a user account.
 */
class Activate extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		return true;
	}

	protected function process()
	{
		$record = $this->record;
		$record->is_activated = true;
		$record->save();

		wd_log_done('!name account is active.', array('!name' => $record->name));

		return true;
	}
}