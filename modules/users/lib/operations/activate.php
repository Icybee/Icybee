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

use ICanBoogie\Operation;

/**
 * Enables a user account.
 */
class ActivateOperation extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
			self::CONTROL_RECORD => true,
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		$record = $this->record;
		$record->is_activated = true;
		$record->save();

		$this->response->message = t('!name account is active.', array('!name' => $record->name));

		return true;
	}
}