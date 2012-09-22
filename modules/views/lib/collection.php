<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\OffsetNotWritable;

/**
 * A collection of view definitions.
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
	private static $instance;

	/**
	 * Returns a unique instance.
	 *
	 * @return Collection
	 */
	public static function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		return self::$instance = new static;
	}

	protected $collection;

	protected function __construct()
	{
		global $core;

		if ($core->config['cache views'])
		{
			$collection = $core->vars['cached_views'];

			if (!$collection)
			{
				$collection = $this->collect();

				$core->vars['cached_views'] = $collection;
			}
		}
		else
		{
			$collection = $this->collect();
		}

		$this->collection = $collection;
	}

	/**
	 * Collects views defined by modules.
	 *
	 * After the views defined by modules have been collected {@link Collection\CollectEvent} is
	 * fired.
	 *
	 * @throws \UnexpectedValueException when the `title`, `type`, `module` or `renders`
	 * properties are empty.
	 *
	 * @return array[string]array
	 */
	protected function collect()
	{
		global $core;

		$collection = array();
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

				$collection[$id . '/' . $type] = $definition;
			}
		}

		new Collection\CollectEvent($this, array('collection' => &$collection));

		$required = array('title', 'type', 'module', 'renders');

		foreach ($collection as $id => &$definition)
		{
			$definition += array
			(
				'access_callback' => null,
				'class' => null,
				'provider' => null,
				'title args' => array()
			);

			foreach ($required as $property)
			{
				if (empty($definition[$property]))
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

		return $collection;
	}

	/**
	 * Checks if a view exists.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}

	/**
	 * Returns the definition of a view.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($id)
	{
		if (!$this->offsetExists($id))
		{
			throw new Collection\ViewNotDefined($id);
		}

		return $this->collection[$id];
	}

	/**
	 * @throws OffsetNotWritable in attempt to set an offset.
	 */
	public function offsetSet($offset, $value)
	{
		throw new OffsetNotWritable(array($offset, $this));
	}

	/**
	 * @throws OffsetNotWritable in attempt to unset an offset.
	 */
	public function offsetUnset($offset)
	{
		throw new OffsetNotWritable(array($offset, $this));
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}
}

namespace Icybee\Modules\Views\Collection;

/**
 * Event class for the event `Icybee\Modules\Views\Collection::collect`.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the collection to alter.
	 *
	 * @var array[string]array
	 */
	public $collection;

	/**
	 * The event is constructed with the type 'collect'.
	 *
	 * @param \Icybee\Modules\Views\Collection $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Views\Collection $target, array $properties)
	{
		parent::__construct($target, 'collect', $properties);
	}
}

/**
 * Exception thrown when a view is not defined.
 */
class ViewNotDefined extends \RuntimeException
{
	public function __construct($id, $code=500, \Exception $previous=null)
	{
		parent::__construct("View not defined: $id.", $code, $previous);
	}
}