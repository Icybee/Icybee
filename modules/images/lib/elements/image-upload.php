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

use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge\Element;

class ImageUpload extends \Icybee\Modules\Files\FileUpload
{
	const THUMBNAIL_WIDTH = 64;
	const THUMBNAIL_HEIGHT = 64;

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('../../public/slimbox.js');
		$document->css->add('../../public/slimbox.css');
		$document->css->add('image-upload.css');
	}

	protected function preview($path)
	{
		global $core;

		$w = $this->w;
		$h = $this->h;

		$url = Operation::encode
		(
			'thumbnailer/get', array
			(
				'src' => $path,
				'w' => $w,
				'h' => $h,
				'format' => 'jpeg',
				'quality' => 90,
				'background' => 'silver,white,medium',
				'uniqid' => uniqid()
			)
		);

		$img = new Element
		(
			'img', array
			(
				'src' => $url,
				'width' => $w,
				'height' => $h,
				'alt' => ''
			)
		);

		$repository = $core->config['repository.temp'];

		if (strpos($path, $repository) === 0)
		{
			return $img;
		}

		return '<a href="' . $path . '&amp;uniqid=' . uniqid() . '" rel="lightbox">' . $img . '</a>';
	}

	protected function details($path)
	{
		$path = $this['value'];

		list($entry_width, $entry_height) = getimagesize($_SERVER['DOCUMENT_ROOT'] . $path);

		$w = $entry_width;
		$h = $entry_height;

		#
		# if the image is larger then the thumbnail dimensions, we resize the image using
		# the "surface" mode.
		#

		$resized = false;

		if (($w * $h) > (self::THUMBNAIL_WIDTH * self::THUMBNAIL_HEIGHT))
		{
			$resized = true;

			$ratio = sqrt($w * $h);

			$w = round($w / $ratio * self::THUMBNAIL_WIDTH);
			$h = round($h / $ratio * self::THUMBNAIL_HEIGHT);
		}

		$this->w = $w;
		$this->h = $h;

		#
		# infos
		#

		$details = array
		(
			'<span title="Path: ' . $path . '">' . basename($path) . '</span>',
			I18n\t('Image size: {0}&times;{1}px', array($entry_width, $entry_height))
		);

		if (($entry_width != $w) || ($entry_height != $h))
		{
			$details[] = I18n\t('Display ratio: :ratio%', array(':ratio' => round(($w * $h) / ($entry_width * $entry_height) * 100)));
		}
		else
		{
			$details[] = I18n\t('Displayed as is');
		}

		$details[] = I18n\format_size(filesize($_SERVER['DOCUMENT_ROOT'] . $path));

		return $details;
	}
}