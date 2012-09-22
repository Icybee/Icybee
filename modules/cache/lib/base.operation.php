<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

use ICanBoogie\Errors;
use ICanBoogie\Exception;

/**
 * @property-read Collection $collection Caches collection.
 */
abstract class BaseOperation extends \ICanBoogie\Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::get_controls();
	}

	protected function volatile_get_collection()
	{
		return Collection::get();
	}

	protected function validate(Errors $errors)
	{
		return true;
	}
}