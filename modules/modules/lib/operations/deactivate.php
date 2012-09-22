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

/**
 * Disable modules.
 */
class DeactivateOperation extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::get_controls();
	}

	/**
	 * Only modules which are not used by other modules can be disabled.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		if ($this->key)
		{
			foreach (array_keys($this->key) as $module_id)
			{
				$n = $core->modules->usage($module_id);

				if ($n)
				{
					$errors[] = t
					(
						'The module %title cannot be disabled, :count modules are using it.', array
						(
							'title' => t($module_id, array(), array('scope' => 'module_title')),
							':count' => $n
						)
					);
				}
			}
		}

		return $errors;
	}

	protected function process()
	{
		global $core;

		$enabled = array_keys($core->modules->enabled_modules_descriptors);
		$enabled = array_combine($enabled, $enabled);

		if ($this->key)
		{
			foreach (array_keys($this->key) as $key)
			{
				unset($enabled[$key]);
				unset($core->modules[$key]);
			}
		}

		$core->vars['enabled_modules'] = array_values($enabled);

		$this->response->location = Route::contextualize('/admin/' . (string) $this->module);

		return true;
	}
}