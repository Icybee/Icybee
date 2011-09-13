<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Accessor;

use ICanBoogie;
use ICanBoogie\Module;

/**
 * Accessor class for the modules of the framework.
 */
class Modules extends ICanBoogie\Accessor\Modules
{
	/**
	 * Overrides the method to disable selected modules before they are run.
	 *
	 * Modules are disabled againts a list of enabled modules. The enabled modules list is made
	 * from the "enabled_modules" persistant var and the value of the T_REQUIRED tag,
	 * which forces some modules to always be enabled.
	 *
	 * @see ICanBoogie\Accessor.Modules::run()
	 */
	public function run()
	{
		global $core;

		$enableds = (array) json_decode($core->vars['enabled_modules'], true);

		foreach ($this->descriptors as $module_id => &$descriptor)
		{
			if (!empty($descriptor[Module::T_REQUIRED]) || in_array($module_id, $enableds))
			{
				continue;
			}

			$descriptor[Module::T_DISABLED] = true;
		}

		parent::run();
	}

	/**
	 * Overrides the method to handle the autoloading of the manager's class for the specified
	 * module.
	 *
	 * @see ICanBoogie\Accessor.Modules::index_module()
	 */
	protected function index_module($id, $path)
	{
		$info = parent::index_module($id, $path);

		if (file_exists($path . 'manager.php'))
		{
			$class = 'Icybee\Manager\\' . ICanBoogie\normalize_namespace_part($id);

			$info['autoload'][$class] = $path . 'manager.php';
		}

		return $info;
	}

	public function ids_by_property($tag, $default=null)
	{
		$rc = array();

		foreach ($this->descriptors as $id => $descriptor)
		{
			if (!isset($descriptor[$tag]))
			{
				if ($default === null)
				{
					continue;
				}

				$value = $default;
			}
			else
			{
				$value = $descriptor[$tag];
			}

			$rc[$id] = $value;
		}

		return $rc;
	}
}