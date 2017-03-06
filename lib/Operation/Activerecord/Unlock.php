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

use ICanBoogie\Binding\PrototypedBindings;
use ICanBoogie\ErrorCollection;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * The "unlock" operation is used to unlock a record previously locked using the "lock"
 * operation.
 */
class Unlock extends Operation
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

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		return $errors;
	}

	protected function process()
	{
		return $this->module->unlock_entry($this->key);
	}
}
