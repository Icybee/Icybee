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

		$enableds = $core->vars['enabled_modules'];

		if ($enableds && is_array($enableds))
		{
			$enableds = array_flip($enableds);

			foreach ($this->descriptors as $module_id => &$descriptor)
			{
				if ($descriptor[Module::T_REQUIRED] || isset($enableds[$module_id]))
				{
					$descriptor[Module::T_DISABLED] = false;
				}
			}
		}

		parent::run();
	}

	/**
	 * Overrides the method to handle the autoloading of the manager's class for the specified
	 * module.
	 *
	 * @see ICanBoogie\Accessor.Modules::index_module()
	 */
	protected function index_module(array $descriptor)
	{
		$index = parent::index_module($descriptor);
		$path = $descriptor[Module::T_PATH];

		if (file_exists($path . 'manager.php'))
		{
			$id = $descriptor[Module::T_ID];
			$class = 'Icybee\Manager\\' . ICanBoogie\normalize_namespace_part($id);

			$index['autoload'][$class] = $path . 'manager.php';
		}

		return $index;
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