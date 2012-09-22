<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Modules;

use ICanBoogie\Operation;
use ICanBoogie\Route;

class ActivateOperation extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::get_controls();
	}

	protected function validate(\ICanBoogie\Errors $errors)
	{
		global $core;

		$install_errors = new \ICanBoogie\Errors;

		foreach ((array) $this->key as $key => $dummy)
		{
			try
			{
				$core->modules[$key] = true;
				$module = $core->modules[$key];
				$install_errors->clear();
				$rc = $module->is_installed($install_errors);

				if (!$rc || count($install_errors))
				{
					$module->install($errors);

					\ICanBoogie\log_success('The module %title was installed.', array('title' => $module->title));
				}

				$enabled[$key] = true;
			}
			catch (\Exception $e)
			{
				$core->modules[$key] = false;
				$errors[] = $e->getMessage();
			}
		}

		return count($errors) == 0;
	}

	protected function process()
	{
		global $core;

		$enabled = array_keys($core->modules->enabled_modules_descriptors);
		$enabled = array_flip($enabled);

		foreach ((array) $this->key as $key => $dummy)
		{
			$enabled[$key] = true;
		}

		$core->vars['enabled_modules'] = array_keys($enabled);

		$this->response->location = Route::contextualize('/admin/' . (string) $this->module);

		return true;
	}
}