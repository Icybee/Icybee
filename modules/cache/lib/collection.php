<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

use ICanBoogie\OffsetNotWritable;
use ICanBoogie\OffsetNotReadable;

class Collection implements \IteratorAggregate, \ArrayAccess
{
	static private $instance;

	static public function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		return self::$instance = new static();
	}

	protected $collection = array();

	protected function __construct()
	{
		$collection = array
		(
			'core.catalogs' => new CatalogsCacheManager,
			'core.configs' => new ConfigsCacheManager,
			'core.modules' => new ModulesCacheManager
		);

		new Collection\CollectEvent($this, array('collection' => &$collection));

		$this->collection = $collection;
	}

	function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}

	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}

	/**
	 * Returns a cache.
	 *
	 * @throws CacheNotDefined in attempt to get a cache that is not defined.
	 */
	public function offsetGet($id)
	{
		if (!$this->offsetExists($id))
		{
			throw new CacheNotDefined($id);
		}

		return $this->collection[$id];
	}

	/**
	 * Adds a cache to the collection.
	 *
	 * @throws OffsetNotWritable in attempt to set an offset.
	 */
	public function offsetSet($id, $cache)
	{
		if (!($cache instanceof CacheManagerInterface))
		{
			throw new \InvalidArgumentException('Cache must implements ' . __NAMESPACE__ . '\CacheManagerInterface.');
		}

		$this->collection[$id] = $cache;
	}

	/**
	 * @throws OffsetNotWritable in attempt to unset an offset.
	 */
	public function offsetUnset($offset)
	{
		throw new OffsetNotWritable(array($offset, $this));
	}
}

/**
 * Exception thrown when a cache is not defined.
 */
class CacheNotDefined extends OffsetNotWritable
{

}

namespace Icybee\Modules\Cache\Collection;

/**
 * Event class for the `Icybee\Modules\Cache\Collection::collect` event.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the cache collection.
	 *
	 * @var array
	 */
	public $collection;

	/**
	 * The event is constructed with the type `collect`.
	 *
	 * @param \Icybee\Modules\Cache\Collection $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Cache\Collection $target, array $payload)
	{
		parent::__construct($target, 'collect', $payload);
	}
}