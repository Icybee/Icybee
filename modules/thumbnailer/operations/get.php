<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Thumbnailer;

use ICanBoogie\Exception;
use ICanBoogie\FileCache;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Image;
use ICanBoogie\Operation;

/**
 * @property Module\Thumbnailer $module
 * @property string $repository Path to the thumbnails repository.
 * @property FileCache $cache Thumbnails cache manager.
 */
class Get extends Operation
{
	const VERSION = '1.2.0';

	/**
	 * Configuration for the module.
	 *
	 * - cleanup_interval: The interval between cleanups, in minutes.
	 *
	 * - repository_size: The size of the repository, in Mo.
	 */
	static public $config = array
	(
		'cleanup_interval' => 15,
		'repository_size' => 8
	);

	static protected $defaults = array
	(
		'background' => 'transparent',
		'default' => null,
		'format' => 'jpeg',
		'height' => null,
		'interlace' => false,
		'method' => 'fill',
		'no-upscale' => false,
		'overlay' => null,
		'path' => null,
		'quality' => 85,
		'src' => null,
		'width' => null
	);

	static protected $shorthands = array
	(
		'b' => 'background',
		'd' => 'default',
		'f' => 'format',
		'h' => 'height',
		'i' => 'interlace',
		'm' => 'method',
		'nu' => 'no-upscale',
		'o' => 'overlay',
		'p' => 'path',
		'q' => 'quality',
		's' => 'src',
		'v' => 'version',
		'w' => 'width'
	);

	static public $background;

	public function reset()
	{
		global $core;

		parent::reset();

		$this->module = $core->modules['thumbnailer'];
	}

	/**
	 * Getter for the $repository magic property.
	 */
	protected function __get_repository()
	{
		return $this->module->repository;
	}

	/**
	 * Getter for the $cache magic property.
	 */
	protected function __get_cache()
	{
		return new FileCache
		(
			array
			(
				FileCache::T_REPOSITORY => $this->repository,
				FileCache::T_REPOSITORY_SIZE => self::$config['repository_size'] * 1024
			)
		);
	}

	/**
	 * Parse, filter and sort options.
	 *
	 * @param unknown_type $options
	 * @throws Exception
	 */
	protected function parse_params($params)
	{
		global $core;

		#
		# handle the 'version' parameter
		#

		if (isset($params['v']))
		{
			$params['version'] = $params['v'];
		}

		if (isset($params['version']))
		{
			$version = $params['version'];
			$version_params = json_decode($core->registry['thumbnailer.versions.' . $version], true);

			// COMPAT

			if (!$version_params)
			{
				$version_params = (array) $core->registry['thumbnailer.versions.' . $version . '.'];
			}

			// /COMPAT

			if (!$version_params)
			{
				throw new Exception('Unknown version %version', array('%version' => $version), 404);
			}

			$params += $version_params;

			unset($params['version']);
		}

		#
		# transform shorthands
		#

		foreach (self::$shorthands as $shorthand => $full)
		{
			if (isset($params[$shorthand]))
			{
				$params[$full] = $params[$shorthand];
			}
		}

		#
		# add defaults so that all options are defined
		#

		$params += self::$defaults;

		if (empty($params['background']))
		{
			$params['background'] = 'transparent';
		}

		if ($params['format'] == 'jpeg' && $params['background'] == 'transparent')
		{
			$params['background'] = 'white';
		}

		#
		# The parameters are filtered and sorted, making extraneous parameters and parameters order
		# non important.
		#

		$params = array_intersect_key($params, self::$defaults);

		ksort($params);

		#
		# check options
		#

		$m = $params['method'];
		$w = $params['width'];
		$h = $params['height'];

		switch ($m)
		{
			case Image::RESIZE_CONSTRAINED:
			case Image::RESIZE_FILL:
			case Image::RESIZE_FIT:
			case Image::RESIZE_SURFACE:
			{
				if (!$w || !$h)
				{
					throw new Exception
					(
						'Missing width or height for the %method method: %width × %height', array
						(
							'%method' => $m,
							'%width' => $w,
							'%height' => $h
						)
					);
				}
			}
			break;
		}

		return $params;
	}

	/**
	 * Returns the location of the thumbnail on the server, relative to the document root.
	 *
	 * The thumbnail is created using the parameters supplied, if it is not already available in
	 * the cache.
	 *
	 * @param array $params
	 * @throws HTTPException
	 */
	public function get(array $params=array())
	{
		$params = $this->parse_params($params);

		#
		# We check if the source file exists
		#

		$src = $params['src'];
		$path = $params['path'];

		if (!$src)
		{
			throw new HTTPException('Missing thumbnail source.', array(), 404);
		}

		$src = $path . $src;
		$location = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $src;

		if (!is_file($location))
		{
			$default = $params['default'];

			#
			# use the provided default file is defined
			#

			if (!$default)
			{
				throw new HTTPException('Thumbnail source not found: %src', array('%src' => $src), 404);
			}

			$src = $path . $default;
			$location = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $src;

			if (!is_file($location))
			{
				throw new HTTPException('Thumbnail source (default) not found: %src', array('%src' => $src), 404);
			}
		}

		#
		# We create a unique key for the thumbnail, using the image information
		# and the options used to create the thumbnail.
		#

		$key = filemtime($location) . '#' . filesize($location) . '#' . json_encode($params);
		$key = sha1($key) . '.' . $params['format'];

		#
		# Use the cache object to get the file
		#

		return $this->cache->get($key, array($this, 'get_construct'), array($location, $params));
	}

	/**
	 * Constructor for the cache entry.
	 *
	 * @param FileCache $cache The cache object.
	 * @param string $destination The file to create.
	 * @param array $userdata An array with the path of the original image and the options to use
	 * to create the thumbnail.
	 * @throws Exception
	 */
	public function get_construct(FileCache $cache, $destination, $userdata)
	{
		list($path, $options) = $userdata;

		$callback = null;

		if ($options['background'] != 'transparent')
		{
			self::$background = self::decode_background($options['background']);

			$callback = array(__CLASS__, 'fill_callback');
		}

        $image = Image::load($path, $info);

		if (!$image)
		{
			throw new Exception('Unable to load image from file %path', array('%path' => $path));
		}

		#
		# resize image
		#

		$w = $options['width'];
		$h = $options['height'];

		list($ow, $oh) = $info;

		$method = $options['method'];

		if ($options['no-upscale'])
		{
			if ($method == Image::RESIZE_SURFACE)
			{
				if ($w * $h > $ow * $oh)
				{
					$w = $ow;
					$h = $oh;
				}
			}
			else
			{
				if ($w > $ow)
				{
					$w = $ow;
				}

				if ($h > $oh)
				{
					$h = $ow;
				}
			}
		}

        $image = Image::resize($image, $w, $h, $method, $callback);

		if (!$image)
		{
			throw new Exception
			(
				'Unable to resize image for file %path with options: !options', array
				(
					'%path' => $path,
					'!options' => $options
				)
			);
		}

		#
		# apply the overlay
		#

		if ($options['overlay'])
		{
			$overlay_file = $_SERVER['DOCUMENT_ROOT'] . $options['overlay'];

			list($o_w, $o_h) = getimagesize($overlay_file);

			$overlay_source = imagecreatefrompng($overlay_file);

			imagecopyresampled($image, $overlay_source, 0, 0, 0, 0, $w, $h, $o_w, $o_h);
		}

		#
		# interlace
		#

		if ($options['interlace'])
		{
			imageinterlace($image, true);
		}

        #
        # choose export format
        #

		$format = $options['format'];

		static $functions = array
		(
	        'gif' => 'imagegif',
	        'jpeg' => 'imagejpeg',
	        'png' => 'imagepng'
        );

        $function = $functions[$format];
        $args = array($image, $destination);

        if ($format == 'jpeg')
        {
        	#
        	# add quality option for the 'jpeg' format
        	#

        	$args[] = $options['quality'];
        }
        else if ($format == 'png' && !$callback)
        {
        	#
        	# If there is no background callback defined, the image is defined as transparent in
        	# order to obtain a transparent thumbnail when the resulting image is centered.
        	#

        	imagealphablending($image, false);
        	imagesavealpha($image, true);
        }

        $rc = call_user_func_array($function, $args);

        imagedestroy($image);

        if (!$rc)
        {
        	throw new Exception('Unable to save thumbnail');
        }

        return $destination;
	}

	protected function validate()
	{
		return true;
	}

	/**
	 * Periodically clears the cache.
	 */
	public function clear_cache()
	{
		$marker = $_SERVER['DOCUMENT_ROOT'] . $this->repository . '/.cleanup';

		$time = file_exists($marker) ? filemtime($marker) : 0;
		$interval = self::$config['cleanup_interval'] * 60;
		$now = time();

		if ($time + $interval > $now)
		{
			return;
		}

		$this->cache->clean();

		touch($marker);
	}

	/**
	 * Operation interface to the @get() method.
	 *
	 * The function uses the @get() method to obtain the location of the image version.
	 * A HTTP redirection is made to the location of the image.
	 *
	 * A HTTPException exception with code 404 is thrown if the function fails to obtain the
	 * location of the image version.
	 *
	 * @throws HTTPException
	 */
	protected function process()
	{
		$this->clear_cache();
		$this->rescue_uri();

		$location = $this->get($this->params);

		if (!$location)
		{
			throw new HTTPException('Unable to create thumbnail for: %src', array('%src' => $this->params['src']), 404);
		}

		$server_location = $_SERVER['DOCUMENT_ROOT'] . $location;

		$stat = stat($server_location);
		$etag = md5($location);

		#
		# The expiration date is set to seven days.
		#

		session_cache_limiter('public');
		session_cache_expire(60 * 24 * 7);

		header('Date: ' . gmdate('D, d M Y H:i:s', $stat['ctime']) . ' GMT');
		header('X-Generated-By: Icybee-Thumbnailer/' . self::VERSION);
		header('Etag: ' . $etag);
		header('Cache-Control: public');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 24 * 7) . ' GMT');

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && isset($_SERVER['HTTP_IF_NONE_MATCH'])
		&& (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $stat['mtime'] || trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag))
		{
			header('HTTP/1.1 304 Not Modified');

			#
			# WARNING: do *not* send any data after that
			#
		}
		else
		{
			$pos = strrpos($location, '.');
			$type = substr($location, $pos + 1);

			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $stat['mtime']) . ' GMT');
		    header('Content-Type: image/' . $type);

		    $fh = fopen($server_location, 'rb');

			fpassthru($fh);

			fclose($fh);
	    }

		$this->terminus = true;

		return $location;
	}

	static private function decode_background($background)
	{
		$parts = explode(',', $background);

		$parts[0] = Image::decode_color($parts[0]);

		if (count($parts) == 1)
		{
			return array($parts[0], null, 0);
		}

		$parts[1] = Image::decode_color($parts[1]);

		return $parts;
	}

	static public function fill_callback($image, $w, $h)
	{
		#
		# We create Image::drawGrid() arguments from the dimensions of the image
		# and the values passed using the 'background' parameter.
		#

		$args = (array) self::$background;

		array_unshift($args, $image, 0, 0, $w - 1, $h - 1);

		call_user_func_array('ICanBoogie\Image::draw_grid', $args);
	}

	/**
	 * Under some strange circumstances, IE6 uses URL with encoded entities. This function tries
	 * to rescue the bullied URIs.
	 *
	 * The decoded parameters are set in the operation's params property.
	 */
	private function rescue_uri()
	{
		$query = $_SERVER['QUERY_STRING'];

		if (strpos($query, '&amp;') === false)
		{
			return;
		}

		$query = html_entity_decode($query);

		$rc = parse_str($query, $this->params);
	}
}