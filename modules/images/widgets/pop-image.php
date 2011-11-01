<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Widget;

use BrickRouge\Element;

class PopImage extends PopNode
{
	const T_PREVIEW_WIDTH = '#preview-width';
	const T_PREVIEW_HEIGHT = '#preview-height';

	public function __construct($tags=array(), $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				self::T_PREVIEW_WIDTH => 64,
				self::T_PREVIEW_HEIGHT => 64,
				self::T_CONSTRUCTOR => 'images',
				self::T_PLACEHOLDER => 'Sélectionner une image'
			)
		);

		$this->dataset = array
		(
			'adjust' => 'adjust-image',
			'preview-width' => $this->get(self::T_PREVIEW_WIDTH),
			'preview-height' => $this->get(self::T_PREVIEW_HEIGHT)
		)

		+ $this->dataset;
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('pop-image.css');
		$document->js->add('pop-image.js');
	}

	protected function getEntry($model, $value)
	{
		return $model->where('path = ? OR title = ? OR slug = ?', $value, $value, $value)->order('created DESC')->one;
	}

	protected function getPreview($record)
	{
		$w = $this->get(self::T_PREVIEW_WIDTH, 64);
		$h = $this->get(self::T_PREVIEW_HEIGHT, 64);

		$rc = '<div class="preview">' . new Element
		(
			'img', array
			(
				'src' => $record ? $record->thumbnail("w:$w;h:$h;m:surface")->url : null,
				'alt' => ''
			)
		)

		. '</div>';

		return $rc . parent::getPreview($record);
	}
}