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

class AdjustThumbnail extends \BrickRouge\Widget
{
	public function __construct($tags)
	{
		parent::__construct
		(
			'div', $tags + array
			(
				Element::T_CHILDREN => array
				(
					$this->adjust_image = new AdjustImage(array()),
					$this->adjust_thumbnail_options = new AdjustThumbnailOptions(array())
				),

				'class' => 'adjust'
			)
		);
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('adjust-thumbnail.js');
		$document->css->add('adjust-thumbnail.css');
	}

	public function render_inner_html()
	{
		return parent::render_inner_html() . '<div class="more">✔</div>';
	}
}