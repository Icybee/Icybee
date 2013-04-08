<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Images;

/**
 * An image.
 *
 * @method \ICanBoogie\Modules\Thumbnailer\Thumbnail thumbnail()
 */
class Image extends \Icybee\Modules\Files\File
{
	const WIDTH = 'width';
	const HEIGHT = 'height';
	const ALT = 'alt';

	/**
	 * Width of the image in pixels.
	 *
	 * @var int
	 */
	public $width;

	/**
	 * Height of the image in pixels.
	 *
	 * @var int
	 */
	public $height;

	/**
	 * Alternative text, used when the image cannot be displayed.
	 *
	 * @var string
	 */
	public $alt;

	/**
	 * The model defaults to `images`.
	 *
	 * @param string $model
	 */
	public function __construct($model='images')
	{
		parent::__construct($model);
	}

	/**
	 * Returns an `IMG` element.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return '<img src="' . \ICanBoogie\escape($this->path) . '" alt="' . \ICanBoogie\escape($this->alt) . '" />';
	}
}