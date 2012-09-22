<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use ICanBoogie\Core;
use ICanBoogie\Exception;
use ICanBoogie\OffsetNotReadable;
use ICanBoogie\OffsetNotWritable;

/**
 * Editors collection.
 *
 * Editors are collected by synthesizing the `editors` config. The
 * `Icybee\Modules\Editor\Collection::alter` is fired to allow alteration of the collection.
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
	static private $instance;

	/**
	 * Returns a unique instance.
	 *
	 * @return Views
	 */
	static public function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		return self::$instance = new static;
	}

	protected $collection;
	protected $editors;

	/**
	 * Creates the collection.
	 */
	protected function __construct()
	{
		$this->collection = $this->collect();
	}

	/**
	 * Collects the editor definitions.
	 *
	 * Editor difinitions are collected from the `editors` config and the
	 * {@link Collection\AlterEvent} is fired to alter the collection.
	 *
	 * @throws \UnexpectedValueException when one of the following required key is missing:
	 * interface_class, editor_class, title.
	 *
	 * @return array
	 */
	protected function collect()
	{
		$collection = (array) Core::get()->configs->synthesize('editors', 'merge');

		new Collection\AlterEvent($this, array(&$collection));

		return $collection;
	}

	/**
	 * Checks if a editor exists.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		if ($offset == 'moo') // COMPAT
		{
			$offset = 'rte';
		}
		else if ($offset == 'adjustimage')
		{
			$offset = 'image';
		}

		return isset($this->collection[$offset]);
	}

	/**
	 * Returns the definition of an editor.
	 *
	 * @throws OffsetNotReadable in attempt to use an undefined editor.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		if ($offset == 'moo') // COMPAT
		{
			$offset = 'rte';
		}
		else if ($offset == 'adjustimage')
		{
			$offset = 'image';
		}

		if (isset($this->editors[$offset]))
		{
			return $this->editors[$offset];
		}

		if (!$this->offsetExists($offset))
		{
			throw new OffsetNotReadable(array($offset, $this));
		}

		$class = $this->collection[$offset];
		$editor = new $class;

		return $this->editors[$offset] = $editor;
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

namespace Icybee\Modules\Editor\Collection;

/**
 * Event class for the `Icybee\Modules\Editor\Collection::alter` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the collection to alter.
	 *
	 * @var array[string]string
	 */
	public $collection;

	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param \Icybee\Modules\Editor\Collection $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Editor\Collection $target, array $properties)
	{
		parent::__construct($target, 'alter', $properties);
	}
}