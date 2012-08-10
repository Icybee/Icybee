<?php

namespace Icybee\Rendering;

use BlueTihi\render;

use ICanBoogie\Exception;

class PropertyRenderers implements \IteratorIterator, \ArrayAccess
{
	/**
	 * Singleton instance of the class.
	 *
	 * @var PropertyRenderers
	 */
	protected static $instance;

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return Events
	 */
	public static function get()
	{
		if (!self::$instance)
		{
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Synthesizes events config.
	 *
	 * Events are retrieved from the "hooks" config, under the "events" namespace.
	 *
	 * @param array $fragments
	 * @throws \InvalidArgumentException when a callback is not properly defined.
	 *
	 * @return array[string]array
	 */
	public static function synthesize_config(array $fragments)
	{
		$collection = array();

		foreach ($fragments as $path => $fragment)
		{
			if (empty($fragment['properties']))
			{
				continue;
			}

			foreach ($fragment['properties'] as $key => $callback)
			{
				if (!strpos($key, '::'))
				{
					throw new \InvalidArgumentException(format
					(
						'Property definition must be <code>{class}/{property}</code> given: :key in %path', array
						(
							'key' => $key,
							'path' => $path . 'config/hooks.php'
						)
					));
				}

				list($class, $property) = explode('::', $key);

				$collection[$property][$class][] = $callback;
			}
		}

		return $collection;
	}

	protected $collection = array();

	/**
	 * Obtains the collection for the config synthesizer.
	 */
	protected function __construct()
	{
		global $core;

		$this->collection = $core->configs['rendering.properties'];

		// TODO-20120713: fire an event to alter the collection
	}

	/**
	 * Returns an iterator for callbacks.
	 *
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}

	/**
	 * Checks if a callback exists for a class+property.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}

	/**
	 * Returns the callbacks for a class+property.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		list($class, $property) = explode('::', $offset);
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

class Rendering
{
	public static function render($source, array $options=array())
	{

	}
}

return array
(
	'properties' => array
	(
		'ICanBoogie\ActiveRecord\Node::title' => 'callback'
	),

	'records' => array
	(
		'ICanBoogie\ActiveRecord\Node' => 'callback'
	),

	'template' => array
	(
		'php' => array
		(
			'class' => 'Engine'
		)
	)
);
