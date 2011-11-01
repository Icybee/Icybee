<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Users\Roles;

use ICanBoogie\Module;

class Delete extends \ICanBoogie\Operation\ActiveRecord\Delete
{
	/**
	 * Controls for the operation: permission(manage), record and ownership.
	 *
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
			self::CONTROL_RECORD => true
		)

		+ parent::__get_controls();
	}

	/**
	 * The visitor (1) and user (2) roles cannot be deleted.
	 *
	 * @see ICanBoogie\Operation\ActiveRecord.Delete::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		if ($this->key == 1 || $this->key == 2)
		{
			$errors[] = 'The <q>visitor</q> and <q>user</q> roles cannot be deleted';

			return false;
		}

		return parent::validate();
	}
}