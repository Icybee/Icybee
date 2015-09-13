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

use ICanBoogie\Errors;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use Icybee\Binding\PrototypedBindings;

/**
 * The "lock" operation is used to obtain an exclusive lock on a record.
 */
class Lock extends Operation
{
	use PrototypedBindings;

	protected function action(Request $request)
	{
		$this->module = $this->app->modules[$this->request['module']];
		$this->key = $this->request['key'];

		return parent::action($request);
	}

	protected function get_controls()
	{
		return [

			self::CONTROL_PERMISSION => Module::PERMISSION_MAINTAIN,
			self::CONTROL_OWNERSHIP => true

		] + parent::get_controls();
	}

	protected function validate(Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		return $this->module->lock_entry($this->key);
	}
}
