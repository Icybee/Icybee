<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Event;

const CACHE_VERSIONS = true;

class Versions implements \ArrayAccess, \IteratorAggregate
{
	private static $instance;

	public static $defaults = array
	(
		'background' => 'transparent',
		'default' => null,
		'format' => 'jpeg',
		'filter' => null,
		'height' => null,
		'method' => 'fill',
		'no-interlace' => false,
		'no-upscale' => false,
		'overlay' => null,
		'path' => null,
		'quality' => 85,
		'src' => null,
		'width' => null
	);

	public static $shorthands = array
	(
		'b' => 'background',
		'd' => 'default',
		'f' => 'format',
		'ft' => 'filter',
		'h' => 'height',
		'm' => 'method',
		'ni' => 'no-interlace',
		'nu' => 'no-upscale',
		'o' => 'overlay',
		'p' => 'path',
		'q' => 'quality',
		's' => 'src',
		'v' => 'version',
		'w' => 'width'
	);

	/**
	 * Returns a unique instance.
	 *
	 * @return Versions
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

	protected $versions;

	protected function __construct()
	{
		global $core;

		if (CACHE_VERSIONS)
		{
			$versions = $core->vars['cached_thumbnailer_versions'];

			if (!$versions)
			{
				$versions = $this->collect();

				$core->vars['cached_thumbnailer_versions'] = $versions;
			}
		}
		else
		{
			$versions = $this->collect();
		}

		$this->versions = $versions;
	}

	/**
	 * Collects versions.
	 *
	 * After the versions have been collected the object fires the "alter" event, with the
	 * following parameter:
	 *
	 * - (array) &versions: The versions to alter.
	 *
	 * @throws \UnexpectedValueException when the `title`, `type`, `module` or `renders`
	 * properties are empty.
	 *
	 * @return array[string]array
	 */
	protected function collect()
	{
		global $core;

		$versions = array();
		$definitions = $core->registry->select('SUBSTR(name, LENGTH("thumbnailer.versions.") + 1) as name, value')->where('name LIKE ?', 'thumbnailer.versions.%')->pairs;

		foreach ($definitions as $name => $options)
		{
			if (!$options || !is_string($options) || $options{0} != '{')
			{
				\ICanBoogie\log_error('bad version: %name, :options', array('name' => $name, 'options' => $options));

				continue;
			}

			$versions[$name] = self::normalize_options(json_decode($options, true));
		}

		Event::fire('alter', array('versions' => &$versions), $this);

		return $versions;
	}

	/**
	 * Normalizes thumbnail options.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public static function normalize_options(array $options)
	{
		foreach (self::$shorthands as $shorthand => $full)
		{
			if (isset($options[$shorthand]))
			{
				$options[$full] = $options[$shorthand];
			}
		}

		#
		# add defaults so that all options are defined
		#

		$options += self::$defaults;

		#
		# The parameters are filtered and sorted, making extraneous parameters and parameters order
		# non important.
		#

		$options = array_intersect_key($options, self::$defaults);

		ksort($options);

		return $options;
	}

	/**
	 * Filter thumbnail options.
	 *
	 * Options than match default values are removed. The options are normalized using
	 * {@link normalize_options()} before they are filtered.
	 *
	 * @param array $options
	 *
	 * @return array The filtered thumbnail options.
	 */
	static public function filter_options(array $options)
	{
		return array_diff_assoc(self::normalize_options($options), self::$defaults);
	}

	/**
	 * Shorten option names.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	static public function shorten_options(array $options)
	{
		$shorten_options = array();
		$shorthands = array_flip(self::$shorthands);

		foreach (self::filter_options($options) as $name => $value)
		{
			$shorten_options[$shorthands[$name]] = $value;
		}

		return $shorten_options;
	}

	/**
	 * Checks if a version exists.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->versions[$offset]);
	}

	/**
	 * Returns the definition of a version.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->versions[$offset] : null;
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
		return new \ArrayIterator($this->versions);
	}
}