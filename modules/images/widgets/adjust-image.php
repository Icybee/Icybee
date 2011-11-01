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

use ICanBoogie\ActiveRecord;
use BrickRouge\Element;

class AdjustImage extends AdjustNode
{
	public function __construct($tags=array(), $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				self::T_CONSTRUCTOR => 'images'
			)
		);

		$this->dataset['adjust'] = 'adjust-image';
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('adjust-image.css');
		$document->js->add('adjust-image.js');

		$document->css->add('../public/manage.css');
		$document->js->add('../public/manage.js');
	}

	protected function get_records($constructor, array $options, $limit=16)
	{
		return parent::get_records($constructor, $options, $limit);
	}

	protected function format_record(ActiveRecord\Node $record, $selected, array $range, array $options)
	{
		$recordid = $record->nid;

		return new Element
		(
			'li', array
			(
				Element::T_CHILDREN => array
				(
					new Element
					(
						'img', array
						(
							Element::T_DATASET => array
							(
								'nid' => $recordid,
								'pop-preview-delay' => 1000,
								'pop-preview-target' => '.widget-adjust-image'
							),

							'src' => $record->thumbnail('w:64;h:64')->url,
							'alt' => $record->alt,
							'width' => 64,
							'height' => 64,
							'class' => 'pop-preview'
						)
					)
				),

				Element::T_DATASET => array
				(
					ActiveRecord\Image::NID => $recordid,
					ActiveRecord\Image::TITLE => $record->title,
					ActiveRecord\Image::PATH => $record->path
				),

				'class' => $recordid == $selected ? 'selected' : null
			)
		);
	}

	public function get_results(array $options=array(), $constructor='images')
	{
		return parent::get_results($options, $constructor);
	}
}