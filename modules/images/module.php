<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Images;

use ICanBoogie\Image;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class Module extends \ICanBoogie\Modules\Files\Module
{
	const ICON_WIDTH = 24;
	const ICON_HEIGHT = 24;
	const THUMBNAIL_WIDTH = 200;
	const THUMBNAIL_HEIGHT = 200;

	protected $accept = array
	(
		'image/gif', 'image/png', 'image/jpeg'
	);

	protected $uploader_class = 'BrickRouge\Widget\ImageUpload';

	/*
	public function install(\ICanBoogie\Errors $errors)
	{
		global $core;

		$registry = $core->registry;

		#
		# we use 'images' instead of 'this' to avoid problems with inheritence
		#

		$registry->set
		(
			'thumbnailer.versions.$icon', array
			(
				'w' => self::ICON_WIDTH,
				'h' => self::ICON_HEIGHT,
				'format' => 'png'
			)
		);

		$registry->set
		(
			'thumbnailer.versions.$popup', array
			(
				'w' => self::THUMBNAIL_WIDTH,
				'h' => self::THUMBNAIL_HEIGHT,
				'method' => Image::RESIZE_SURFACE,
				'no-upscale' => true,
				'quality' => 90
			)
		);

		return parent::install($errors);
	}
	*/

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', 'uid', 'is_online', 'surface', 'size', 'modified'
				)
			)
		);
	}

	protected function block_gallery()
	{
		return new GalleryManager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'surface', 'size', 'modified'),
				Manager::T_ORDER_BY => 'title'
			)
		);
	}

	protected function block_edit(array $properties, $permission, array $options=array())
	{
		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission, $options), array
			(
				Element::CHILDREN => array
				(
					'alt' => new Text
					(
						array
						(
							Form::LABEL => '.alt'
						)
					)
				)
			)
		);
	}

	public function adjust_createEntry($entry)
	{
		$img = new Element
		(
			'img', array
			(
				'src' => $entry->thumbnail('$icon'),
				'alt' => '',
				'width' => self::ICON_WIDTH,
				'height' => self::ICON_HEIGHT
			)
		);

		$rc = $img . ' ' . parent::adjust_createEntry($entry);

		$path = wd_entities($entry->path);

		// TODO-20110108: use a dataset

		$rc .= '<input type="hidden" class="preview" value="' . $path . '" />';
		$rc .= '<input type="hidden" class="path" value="' . $path . '" />';

		return $rc;
	}

	protected function block_adjust(array $params)
	{
		return new WdAdjustImageWidget
		(
			array
			(
				\WdAdjustImageWidget::T_CONSTRUCTOR => $this->id,
				Element::DESCRIPTION => null,

				'value' => isset($params['value']) ? $params['value'] : null
			)
		);
	}
}

class resources_images_adjustimage_WdPager extends \BrickRouge\Pager
{
	protected function getURL($n)
	{
		return '#' . $n;
	}
}