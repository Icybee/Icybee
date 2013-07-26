<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Module;

/**
 * Accessor class for the modules of the framework.
 */
class Modules extends \ICanBoogie\Modules
{
	/**
	 * Disables selected modules.
	 *
	 * Modules are disabled againts a list of enabled modules. The enabled modules list is made
	 * from the `enabled_modules` persistant variable and the value of the {@link T_REQUIRED}
	 * tag, which forces some modules to always be enabled.
	 */
	protected function get_index()
	{
		global $core;

		$index = parent::get_index();
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