<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Module;

use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

/**
 * Save the configuration of the module.
 *
 * There are two spaces for the configuration to be saved in: a local space and a global
 * space.
 *
 * Configuration in the local space is saved in the `metas` of the working site object, whereas
 * the configuration in the global space is saved in the registry.
 *
 */
class Config extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
			self::CONTROL_FORM => true
		)

		+ parent::__get_controls();
	}

	/**
	 * Parse the operation parameters to create the key/value pairs to save in the "global" and
	 * "local" config spaces.
	 *
	 * @see ICanBoogie.Operation::__get_properties()
	 */
	protected function __get_properties()
	{
		$properties = array_intersect_key($this->params, array('global' => true, 'local' => true));

		Event::fire('properties:before', array('properties' => &$properties), $this);

		return $properties;
	}

	protected function validate()
	{
		return true;
	}

	protected function process()
	{
		global $core;

		$params = $this->properties;

		if (isset($params['global']))
		{
			$registry = $core->registry;

			foreach ($params['global'] as $name => $value)
			{
				$registry[$name] = $value;
			}
		}

		if (isset($params['local']))
		{
			$site = $core->site;

			foreach ($params['local'] as $name => $value)
			{
				if (is_array($value))
				{
					foreach ($value as $subname => $subvalue)
					{
						$site->metas[$name . '.' . $subname] = $subvalue;
					}

					continue;
				}

				$site->metas[$name] = $value;
			}
		}

		wd_log_done("La configuration a été renregistrée");

		$this->location = $_SERVER['REQUEST_URI'];

		return true;
	}
}