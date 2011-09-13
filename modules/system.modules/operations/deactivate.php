<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\System\Modules;

use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Route;

class Deactivate extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		return true;
	}

	protected function process()
	{
		global $core;

		$enabled = json_decode($core->vars['enabled_modules'], true);
		$enabled = $enabled ? array_flip($enabled) : array();

		foreach ((array) $this->key as $key => $dummy)
		{
			unset($enabled[$key]);
		}

		$core->vars['enabled_modules'] = json_encode(array_keys($enabled));

		$this->location = Route::contextualize('/admin/' . (string) $this->module);

		return true;
	}
}