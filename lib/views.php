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

use ICanBoogie\Event;
use ICanBoogie\Exception;

/**
 * The views defined by the enabled modules.
 */
class Views implements \ArrayAccess, \IteratorAggregate
{
	private static $instance;

	/**
	 * Returns a unique instance.
	 *
	 * @return Views
	 */
	public static function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		$class = get_called_class();

		return self::$instance = new $class;
	}

	protected $views;

	protected function __construct()
	{
		global $core;

		if (CACHE_VIEWS)
		{
			$views = $core->vars['cached_views'];

			if (!$views)
			{
				$views = $this->collect();

				$core->vars['cached_views'] = $views;
			}
		}
		else
		{
			$views = $this->collect();
		}

		$this->views = $views;
	}

	/**
	 * Collects views defined by modules.
	 *
	 * After the views defined by modules have been collected the object fires the "alter" event,
	 * with the following parameter:
	 *
	 * - (array) &views: The views to alter.
	 *
	 * @throws \UnexpectedValueException when the `title`, `type`, `module` or `renders`
	 * properties are empty.
	 *
	 * @return array[string]array
	 */
	protected function collect()
	{
		global $core;

		$modules = $core->modules;

		foreach ($modules->enabled_modules_descriptors as $id => $descriptor)
		{
			$module = $modules[$id];

			if (!$module->has_property('views'))
			{
				continue;
			}

			$module_views = $module->views;

			foreach ($module_views as $type => $definition)
			{
				$definition += array
				(
					'module' => $id,
					'type' => $type
				);

				$views[$id . '/' . $type] = $definition;
			}
		}

		new Views\AlterEvent($this, array('views' => &$views));

		$required = array('title', 'type', 'module', 'renders');

		foreach ($views as $id => &$view)
		{
			$view += array
			(
				'access_callback' => null,
				'class' => null,
				'title args' => array()
			);

			foreach ($required as $property)
			{
				if (empty($view[$property]))
				{
					throw new \UnexpectedValueException(\ICanBoogie\format
					(
						'%property is empty for the view %id.', array
						(
							'property' => $property,
							'id' => $id
						)
					));
				}
			}
		}

		return $views;
	}

	/**
	 * Checks if a view exists.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->views[$offset]);
	}

	/**
	 * Returns the definition of a view.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->views[$offset] : null;
	}

	public function offsetSet($offset, $value)
	{
		throw new Exception\OffsetNotWritable(array($offset, $this));
	}

	public function offsetUnset($offset)
	{
		throw new Exception\OffsetNotWritable(array($offset, $this));
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->views);
	}
}

namespace Icybee\Views;

/**
 * Event class for the event `Icybee\Views::alter`.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the views to alter.
	 *
	 * @var array[string]array
	 */
	public $views;

	/**
	 * The event is constructed with the type 'alter'.
	 *
	 * @param \Icybee\Views $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Views $target, array $properties)
	{
		parent::__construct($target, 'alter', $properties);
	}
}