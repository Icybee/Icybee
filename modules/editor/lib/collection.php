<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Editor;

use ICanBoogie\Core;
use ICanBoogie\Exception;

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

	protected function __construct()
	{
		$collection = $this->collect();

		$this->collection = $collection;
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
		return isset($this->collection[$offset]);
	}

	/**
	 * Returns the definition of an editor. TODO-20120811: we should return an EditorInterface instance.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		if (isset($this->editors[$offset]))
		{
			return $this->editors[$offset];
		}

		if (!$this->offsetExists($offset))
		{
			throw new Exception\OffsetNotReadable(array($offset, $this));
		}

		$class = $this->collection[$offset];
		$editor = new $class;

		return $this->editors[$offset] = $editor;
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
		return new \ArrayIterator($this->collection);
	}
}

namespace ICanBoogie\Modules\Editor\Collection;

/**
 * Event class for the `ICanBoogie\Modules\Editor\Collection::alter` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the collection to alter.
	 *
	 * @var array[string]array
	 */
	public $collection;

	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param \ICanBoogie\Modules\Editor\Collection $target
	 * @param array $properties
	 */
	public function __construct(\ICanBoogie\Modules\Editor\Collection $target, array $properties)
	{
		parent::__construct($target, 'alter', $properties);
	}
}