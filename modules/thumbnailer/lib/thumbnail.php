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
	/**
	 * The source of the thumbnail.
	 *
	 * @var ICanBoogie\ActiveRecord\Image|int|string
	 */
	public $src;

	/**
	 * Options to created the thumbnail.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Version name of the thumbnail.
	 *
	 * @var string
	 */
	protected $version_name;

	/**
	 * Constructor.
	 *
	 * @param ICanBoogie\ActiveRecord\Image|int|string The souce of the thumbnail.
	 *
	 * @param string|array $options The options to create the thumbnail can be provided as a
	 * version name or an array of options. If a version name is provided, the `image` parameter
	 * must also be provided.
	 *
	 * @param string|array $additionnal_options Additionnal options to created the thumbnail.
	 */
	public function __construct($src, $options, $additionnal_options=null)
	{
		if (is_string($options))
		{
			if (strpos($options, ':') !== false)
			{
				$options = self::decode_options($options);
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

		if ($additionnal_options)
		{
			if (is_string($additionnal_options))
			{
				$additionnal_options = self::decode_options($additionnal_options);
			}

			$this->options = $additionnal_options + $this->options;
		}

		$this->options = Versions::filter_options($this->options);

		$this->src = $src;
	}

	protected static function decode_options($options)
	{
		preg_match_all('#([^:]+):\s*([^;]+);?#', $options, $matches, PREG_PATTERN_ORDER);

		return array_combine($matches[1], $matches[2]);
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

		return Versions::filter_options(json_decode($version, true));
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
		if (!empty($this->options['width']))
		{
			return $this->options['width'];
		}

		$version = $this->version;

		if (!empty($version['width']))
		{
			return $version['width'];
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
		if (!empty($this->options['height']))
		{
			return $this->options['height'];
		}

		$version = $this->version;

		if (!empty($version['height']))
		{
			return $version['height'];
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

		$version = $this->version;

		if (!empty($version['method']))
		{
			return $version['method'];
		}
	}

	protected function volatile_set_method($method)
	{
		$this->options['method'] = $method;
	}

	/**
	 * Returns the thumbnail URL.
	 *
	 * @return string The thumbnail URL.
	 */
	public function volatile_get_url()
	{
		global $core;

		$src = $this->src;
		$options = $this->options;
		$version_name = $this->version_name;
		$url = '/api/';

		$w = $this->w;
		$h = $this->h;
		$method = $this->method;

		if (is_string($src))
		{
			$url .= 'thumbnail';

			$options['src'] = $src;
			$options['version'] = $version_name;

			if ($w || $h)
			{
				$url .= '/';

				if ($w && $h)
				{
					$url .= $w . 'x' . $h;
				}
				else if ($w)
				{
					$url .= $w;
					$method = 'fixed-width';
				}
				else if ($h)
				{
					$url .= 'x' . $h;
					$method = 'fixed-height';
				}

				unset($options['width']);
				unset($options['height']);

				if ($method)
				{
					$url .= '/' . $method;

					unset($options['method']);
				}
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
				if ($w || $h)
				{
					$url .= '/';

					if ($w && $h)
					{
						$url .= $w . 'x' . $h;
					}
					else if ($w)
					{
						$url .= $w;
						$method = 'fixed-width';
					}
					else if ($h)
					{
						$url .= 'x' . $h;
						$method = 'fixed-height';
					}

					unset($options['width']);
					unset($options['height']);

					if ($method)
					{
						$url .= '/' . $method;

						unset($options['method']);
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
			$url .= '?'. http_build_query(Versions::shorten_options($options));
		}

		return $url;
	}

	/**
	 * Convert the thumbnail into a IMG element.
	 *
	 * @param array $attributes
	 *
	 * @return \Brickrouge\Element
	 */
	public function to_element(array $attributes=array())
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
			'img', $attributes + array
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
	 * Return a IMG element that can be inserted as is in the document.
	 *
	 * The `width` and `height` attribute of the element are defined whenever possible. The `alt`
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