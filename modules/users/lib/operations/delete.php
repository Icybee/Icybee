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

use ICanBoogie\I18n\FormattedString;

/**
 * Deletes a user.
 */
class DeleteOperation extends \ICanBoogie\DeleteOperation
{
	protected function validate(\ICanBoogie\Errors $errors)
	{
		if ($this->key == 1)
		{
			$errors['uid'] = new FormattedString("Daddy cannot be deleted.");
		}

		return parent::validate($errors);
	}
}