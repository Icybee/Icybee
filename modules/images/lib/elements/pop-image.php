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

use Brickrouge\Element;

class PopImage extends \ICanBoogie\Modules\Nodes\PopNode
{
	const T_PREVIEW_WIDTH = '#preview-width';
	const T_PREVIEW_HEIGHT = '#preview-height';

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('pop-image.css');
		$document->js->add('pop-image.js');
	}

	public function __construct($tags=array(), $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				self::T_PREVIEW_WIDTH => 64,
				self::T_PREVIEW_HEIGHT => 64,
				self::T_CONSTRUCTOR => 'images',

				'placeholder' => 'Sélectionner une image',

				'data-adjust' => 'adjust-image'
			)
		);
	}

	protected function alter_dataset(array $dataset)
	{
		return parent::alter_dataset
		(
			$dataset + array
			(
				'preview-width' => $this[self::T_PREVIEW_WIDTH],
				'preview-height' => $this[self::T_PREVIEW_HEIGHT],
				'widget-constructor' => 'PopImage'
			)
		);
	}

	protected function getEntry($model, $value)
	{
		return $model->where('path = ? OR title = ? OR slug = ?', $value, $value, $value)->order('created DESC')->one;
	}

	protected function getPreview($record)
	{
		$w = $this[self::T_PREVIEW_WIDTH] ?: 64;
		$h = $this[self::T_PREVIEW_HEIGHT] ?: 64;

		return new Element
		(
			'img', array
			(
				'src' => $record ? $record->thumbnail("w:$w;h:$h;m:surface")->url : null,
				'alt' => ''
			)
		);
	}
}