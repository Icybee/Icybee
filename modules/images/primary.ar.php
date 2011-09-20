<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

class Image extends File
{
	const WIDTH = 'width';
	const HEIGHT = 'height';
	const ALT = 'alt';

	public $width;
	public $height;
	public $alt;

	public function __construct($model='images')
	{
		parent::__construct($model);
	}

	public function __toString()
	{
		return '<img src="' . wd_entities($this->path) . '" alt="' . wd_entities($this->alt) . '" />';
	}
}