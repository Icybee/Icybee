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

		$views = $core->vars['views'];

		if (!$views)
		{
			$views = $this->collect();

			$core->vars['views'] = $views;
		}

		$this->views = $views;
	}

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
					'class' => null,
					'module' => $id,
					'type' => $type
				);

				$views[$id . '/' . $type] = $definition;
			}
		}

		return $views;
	}

	public function offsetExists($offset)
	{
		return isset($this->views[$offset]);
	}

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