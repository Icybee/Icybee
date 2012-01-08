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

class Activate extends Operation
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

		$errors = $this->response->errors;

		$enabled = array_keys($core->modules->enabled_modules_descriptors);
		$enabled = array_combine($enabled, $enabled);

		foreach ((array) $this->key as $key => $dummy)
		{
			try
			{
				$core->modules[$key] = true;
				$module = $core->modules[$key];

				$rc = $module->is_installed($errors);

				if (!$rc || count($errors))
				{
					$module->install($errors);
				}

				$enabled[$key] = $key;
			}
			catch (\Exception $e)
			{
				wd_log_error($e->getMessage());
			}
		}

		$core->vars['enabled_modules'] = json_encode($enabled);

		unset($core->vars['views']);

		$this->response->location = Route::contextualize('/admin/' . (string) $this->module);

		return true;
	}
}