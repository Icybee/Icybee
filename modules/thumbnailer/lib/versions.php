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

			$versions[$name] = self::nomalize_version(json_decode($options, true));
		}

		Event::fire('alter', array('versions' => &$versions), $this);

		return $versions;
	}

	/**
	 * Normalizes the options of a thumbnail version.
	 *
	 * @param array $version
	 *
	 * @return array
	 */
	public static function nomalize_version(array $version)
	{
		foreach (self::$shorthands as $shorthand => $full)
		{
			if (isset($version[$shorthand]))
			{
				$version[$full] = $version[$shorthand];
			}
		}

		#
		# add defaults so that all options are defined
		#

		$version += self::$defaults;

		#
		# The parameters are filtered and sorted, making extraneous parameters and parameters order
		# non important.
		#

		$version = array_intersect_key($version, self::$defaults);

		ksort($version);

		return $version;
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