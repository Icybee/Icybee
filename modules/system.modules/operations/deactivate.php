<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Modules;

use ICanBoogie\Operation;
use ICanBoogie\Route;

class DeactivateOperation extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		global $core;

		$enabled = array_keys($core->modules->enabled_modules_descriptors);
		$enabled = array_combine($enabled, $enabled);

		foreach ((array) $this->key as $key => $dummy)
		{
			unset($enabled[$key]);
		}

		$core->vars['enabled_modules'] = array_values($enabled);

		unset($core->vars['views']);

		$this->response->location = Route::contextualize('/admin/' . (string) $this->module);

		return true;
	}
}