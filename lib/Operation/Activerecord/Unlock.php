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
use Icybee\Binding\PrototypedBindings;

/**
 * The "unlock" operation is used to unlock a record previously locked using the "lock"
 * operation.
 */
class Unlock extends Operation
{
	use PrototypedBindings;

	protected function reset()
	{
		parent::reset();

		$this->module = $this->app->modules[$this->request['module']];
		$this->key = $this->request['key'];
	}

	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_MAINTAIN,
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
		return $this->module->unlock_entry((int) $this->key);
	}
}
