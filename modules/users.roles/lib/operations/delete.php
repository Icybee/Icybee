<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\Roles;

/**
 * Deletes a role.
 */
class DeleteOperation extends \ICanBoogie\DeleteOperation
{
	/**
	 * Modifies the following controls:
	 *
	 *     PERMISSION: ADMINISTER
	 *     OWNERSHIP: false
	 *
	 * @see ICanBoogie\DeleteOperation::get_controls()
	 */
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
			self::CONTROL_OWNERSHIP => false
		)

		+ parent::get_controls();
	}

	/**
	 * The "visitor" (1) and "user" (2) roles cannot be deleted.
	 *
	 * @see ICanBoogie\DeleteOperation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		if ($this->key == 1 || $this->key == 2)
		{
			$errors[] = t('The role %name cannot be deleted.', array('name' => $this->record->name));
		}

		return parent::validate($errors);
	}
}