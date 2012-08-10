<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

use ICanBoogie\Event;
use ICanBoogie\Exception;

class Collection implements \IteratorAggregate, \ArrayAccess
{
	protected $caches = array();

	public function __construct()
	{
		$caches = array
		(
			/*'core.assets' => new CoreAssetsarray
			(
				'title' => 'CSS et Javascript',
				'description' => "Jeux compilés de sources CSS et Javascript.",
				'group' => 'system',
				'state' => $config['cache assets'],
				'size_limit' => false,
				'time_limit' => false,
				'class' => __NAMESPACE__ . '\CoreAssets'
			),*/

			'core.catalogs' => new CatalogsCache,
			'core.configs' => new ConfigsCache,
			'core.modules' => new ModulesCache
		);

		new Collection\AlterEvent($this, array('collection' => &$caches));

		$this->caches = $caches;
	}

	function getIterator()
	{
		return new \ArrayIterator($this->caches);
	}

	public function offsetExists($offset)
	{
		return isset($this->caches[$offset]);
	}

	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset))
		{
			throw new Exception\OffsetNotReadable(array($offset, $this));
		}

		return $this->caches[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new Exception\OffsetNotWritable(array($offset, $this));
	}

	public function offsetUnset($offset)
	{
		throw new Exception\OffsetNotWritable(array($offset, $this));
	}
}

namespace ICanBoogie\Modules\System\Cache\Collection;

/**
 * Event class for the `ICanBoogie\Modules\System\Cache\Collection::alter` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the cache collection.
	 *
	 * @var array
	 */
	public $collection;

	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param \ICanBoogie\Modules\System\Cache\Collection $target
	 * @param array $properties
	 */
	public function __construct(\ICanBoogie\Modules\System\Cache\Collection $target, array $properties)
	{
		parent::__construct($target, 'alter', $properties);
	}
}