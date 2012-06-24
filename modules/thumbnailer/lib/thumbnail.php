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

use ICanBoogie;
use ICanBoogie\ActiveRecord;

use Brickrouge\Element;

/**
 * Cette classes est une aide à la création de miniatures. Elle prend en paramètres une source et
 * un tableau d'option ou le nom d'une version, et permet d'obtenir l'URL de la miniature ou le
 * marqueur IMG de la miniature.
 *
 * La source peut être définie par l'URL d'une image ou une instance de la classe
 * ICanBoogie\ActiveRecord\Image. Les options peuvent être un tableau de paramètres ou le nom
 * d'une version.
 *
 * @property $version array|null Il s'agit des paramètres correspondant à la version.
 * @property $w int|null Largeur de la miniature, extraite des options ou de la version.
 * @property $h int|null Hauteur de la miniature, extraite des options ou de la version.
 * @property $method string|null Méthode de redimensionnement de la miniature, extraite des options
 * ou de la version.
 */
class Thumbnail extends \ICanBoogie\Object
{
	public $src;
	public $options;

	protected $version_name;

	/**
	 * Constructor.
	 *
	 * @param ICanBoogie\ActiveRecord\Image|int|string|null
	 *
	 * @param string|array $options The options to create the thumbnail can be provided as a
	 * version name or an array of options. If a version name is provided, the `image` parameter
	 * must also be provided.
	 */
	public function __construct($src, $options)
	{
		if (is_string($options))
		{
			if (strpos($options, ':') !== false)
			{
				preg_match_all('#([^:]+):\s*([^;]+);?#', $options, $matches, PREG_PATTERN_ORDER);

				$options = array_combine($matches[1], $matches[2]);
			}
			else
			{
				$this->version_name = $options;
			}
		}

		if (is_array($options))
		{
			$this->options = $options;
		}

		$this->src = $src;
	}

	private $_version;

	protected function volatile_get_version()
	{
		global $core;

		if ($this->_version)
		{
			return $this->_version;
		}
		else if (!$this->version_name)
		{
			return;
		}

		$version = $core->registry['thumbnailer.versions.' . $this->version_name];

		if (!$version)
		{
			return;
		}

		return json_decode($version, true);
	}

	/**
	 * Returns the width of the thumbnail.
	 *
	 * The width of the thumbnail is extracted from the options or the version parameters.
	 *
	 * @return int|null The width of the thumbnail or null if it's not available.
	 */
	protected function volatile_get_w()
	{
		if (!empty($this->options['w']))
		{
			return $this->options['w'];
		}

		$version = $this->version;

		if (!empty($version['w']))
		{
			return $version['w'];
		}
	}

	/**
	 * Returns the height of the thumbnail.
	 *
	 * The height of the thumbnail is extracted from the options or the version's parameters.
	 *
	 * @return int|null The height of the thumbnail or null if it's not available.
	 */
	protected function volatile_get_h()
	{
		if (!empty($this->options['h']))
		{
			return $this->options['h'];
		}

		$version = $this->version;

		if (!empty($version['h']))
		{
			return $version['h'];
		}
	}

	/**
	 * Returns the name of the method used to resize the image.
	 *
	 * The resizing method of the thumbnail is extracted from the options or the version's
	 * parameters.
	 *
	 * @return int|null The name of method used to resize the image or null if it's not
	 * available.
	 */
	protected function volatile_get_method()
	{
		if (!empty($this->options['method']))
		{
			return $this->options['method'];
		}
		else if (!empty($this->options['m']))
		{
			return $this->options['m'];
		}

		$version = $this->version;

		if (!empty($version['method']))
		{
			return $version['method'];
		}
	}

	/**
	 * Returns the thumbnail URL.
	 *
	 * @return string The thumbnail URL.
	 */
	public function get_url()
	{
		global $core;

		$src = $this->src;
		$options = $this->options;
		$version_name = $this->version_name;
		$url = '/api/';

		if (is_string($src))
		{
			$url .= 'thumbnail';

			$options['src'] = $src;
			$options['version'] = $version_name;

			if (isset($options['w']) || isset($options['h']))
			{
				$url .= '/';

				if (isset($options['w']) && isset($options['h']))
				{
					$url .= $options['w'] . 'x' . $options['h'];

					unset($options['w']);
					unset($options['h']);
				}
				else if (isset($options['w']))
				{
					$url .= $options['w'];

					unset($options['w']);
				}
				else if (isset($options['h']))
				{
					$url .= $options['h'];

					unset($options['h']);
				}

				if (isset($options['m']))
				{
					$url .= '/' . $options['m'];

					unset($options['m']);
				}

				unset($options['w']);
				unset($options['h']);
			}
		}
		else
		{
			$url .= $src->constructor . '/' . $src->nid;

			if ($version_name)
			{
				$url .= '/thumbnails/' . $version_name;
			}
			else
			{
				if (isset($options['w']) || isset($options['h']))
				{
					if (isset($options['w']) && isset($options['h']))
					{
						$url .= '/' . $options['w'] . 'x' . $options['h'];

						unset($options['w']);
						unset($options['h']);
					}
					else if (isset($options['w']))
					{
						$url .= '/' . $options['w'];

						unset($options['w']);
					}
					else if (isset($options['h']))
					{
						$url .= '/x' . $options['h'];

						unset($options['h']);
					}

					if (isset($options['m']))
					{
						$url .= '/' . $options['m'];

						unset($options['m']);
					}
				}
				else
				{
					$url .= '/thumbnail';
				}
			}
		}

		if ($options)
		{
			$url .= '?'. http_build_query($options);
		}

		return $url;
	}

	public function to_element(array $tags=array())
	{
		$path = $this->src;
		$src = $this->url;
		$alt = '';

		if ($this->src instanceof Image)
		{
			$alt = $this->src->alt;
			$path = $this->src->path;
		}

		$w = $this->w;
		$h = $this->h;

		if (is_string($path))
		{
			list($w, $h) = \ICanBoogie\Image::compute_final_size($w, $h, $this->method, \ICanBoogie\DOCUMENT_ROOT . $path);
		}

		return new Element
		(
			'img', $tags + array
			(
				'src' => $src,
				'alt' => $alt,
				'width' => $w,
				'height' => $h,
				'class' => 'thumbnail'
			)
		);
	}

	/**
	 * Return a IMG marker that can be inserted as is in the document.
	 *
	 * The `width` and `height` attribute of the marker are defined whenever possible. The `alt`
	 * attribute is also defined if the image src is an Image active record.
	 */
	public function __toString()
	{
		try
		{
			return (string) $this->to_element();
		}
		catch (\Exception $e)
		{
			echo \ICanBoogie\Debug::format_alert($e);
		}
	}
}