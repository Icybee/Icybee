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

use ICanBoogie\ErrorCollection;
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
 *
 * Event: properties:before
 * ------------------------
 *
 * The `property:before` event of class `Icybee\Operation\Module\ConfigOperation\BeforePropertiesEvent` is fired by
 * the `Icybee\Operation\Module\ConfigOperation` and its subclasses before the config properties are collected.
 *
 * One can attach a hook to this event to modify the operation request params before they are used
 * to collect the config properties.
 *
 *
 * Event: properties
 * -----------------
 *
 * The `properties` event of class `Icybee\Operation\Module\ConfigOperation\PropertiesEvent` is fired by the
 * `Icybee\Operation\Module\ConfigOperation` and its subclasses after the config properties were collected.
 *
 * One can attach a hook to this event to modify the properties before they are stored.
 */
class ConfigOperation extends Operation
{
	protected function get_controls()
	{
		return [

		self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER,
		self::CONTROL_FORM => true

		] + parent::get_controls();
	}

	/**
	 * Parse the operation parameters to create the key/value pairs to save in the "global" and
	 * "local" config spaces.
	 */
	protected function lazy_get_properties()
	{
		return array_intersect_key($this->request->params, [ 'global' => true, 'local' => true ]);
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
		new ConfigOperation\BeforePropertiesEvent($this, $this->request);

		$properties = $this->properties;

		new ConfigOperation\PropertiesEvent($this, $this->request, $properties);

		if (isset($properties['global']))
		{
			$registry = $this->app->registry;

			foreach ($properties['global'] as $name => $value)
			{
				$registry[$name] = $value;
			}
		}

		if (isset($properties['local']))
		{
			$site = $this->app->site;

			foreach ($properties['local'] as $name => $value)
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

		$this->response->message = "The configuration has been saved.";
		$this->response->location = $this->request->path;

		return true;
	}
}
