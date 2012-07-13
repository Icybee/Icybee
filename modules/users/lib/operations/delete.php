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

/**
 * Deletes a user.
 */
use ICanBoogie\Errors;

class DeleteOperation extends \ICanBoogie\DeleteOperation
{
	protected function validate(Errors $errors)
	{
		if ($this->key == 1)
		{
			$errors['uid'] = t("Daddy cannot be deleted.");
		}

		return parent::validate($errors);
	}
}