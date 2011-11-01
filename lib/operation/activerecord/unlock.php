<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\ActiveRecord;

use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * The "unlock" operation is used to unlock a record previously locked using the "lock"
 * operation.
 */
class Unlock extends Operation
{
	protected function reset()
	{
		global $core;

		parent::reset();

		$this->module = $core->modules[$this->request['module']];
		$this->key = $this->request['key'];
	}

	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_MAINTAIN,
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		return $this->module->unlock_entry((int) $this->key);
	}
}