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
 * The `property:before` event of class `Icybee\ConfigOperation\BeforePropertiesEvent` is fired by
 * the `Icybee\ConfigOperation` and its subclasses before the config properties are collected.
 *
 * One can attach a hook to this event to modify the operation request params before they are used
 * to collect the config properties.
 *
 *
 * Event: properties
 * -----------------
 *
 * The `properties` event of class `Icybee\ConfigOperation\PropertiesEvent` is fired by the
 * `Icybee\ConfigOperation` and its subclasses after the config properties were collected.
 *
 * One can attach a hook to this event to modify the properties before they are stored.
 */
class ConfigOperation extends \ICanBoogie\Operation
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

	protected function validate(\ICanboogie\Errors $errors)
	{
		return !count($errors);
	}

	protected function process()
	{
		global $core;

		new ConfigOperation\BeforePropertiesEvent($this, [ 'request' => $this->request ]);

		$properties = $this->properties;

		new ConfigOperation\PropertiesEvent($this, [ 'request' => $this->request, 'properties' => &$properties ]);

		if (isset($properties['global']))
		{
			$registry = $core->registry;

			foreach ($properties['global'] as $name => $value)
			{
				$registry[$name] = $value;
			}
		}

		if (isset($properties['local']))
		{
			$site = $core->site;

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

namespace Icybee\ConfigOperation;

/**
 * Event class for the `Icybee\ConfigOperation::properties:before` event.
 */
class BeforePropertiesEvent extends \ICanBoogie\Event
{
	/**
	 * The HTTP request.
	 *
	 * @var \ICanBoogie\HTTP\Request
	 */
	public $request;

	/**
	 * The event is constructed with the type `properties:before`.
	 *
	 * @param \Icybee\ConfigOperation $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\ConfigOperation $target, array $payload)
	{
		parent::__construct($target, 'properties:before', $payload);
	}
}

/**
 * Event class for the `Icybee\ConfigOperation::properties` event.
 */
class PropertiesEvent extends \ICanBoogie\Event
{
	/**
	 * The HTTP request.
	 *
	 * @var \ICanBoogie\HTTP\Request
	 */
	public $request;

	/**
	 * Reference to the config properties.
	 *
	 * @var array
	 */
	public $payload;

	/**
	 * The event is constructed with the type `properties`.
	 *
	 * @param \Icybee\ConfigOperation $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\ConfigOperation $target, array $payload)
	{
		parent::__construct($target, 'properties', $payload);
	}
}
