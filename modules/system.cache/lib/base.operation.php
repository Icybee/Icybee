<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

use ICanBoogie\Errors;
use ICanBoogie\Exception;

abstract class BaseOperation extends \ICanBoogie\Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate(Errors $errors)
	{
		return true;
	}
}