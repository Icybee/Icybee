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

class AdjustThumbnail extends \Brickrouge\Widget
{
	protected $adjust_image;
	protected $adjust_thumbnail_options;

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'div', $attributes + array
			(
				Element::CHILDREN => array
				(
					$this->adjust_image = new AdjustImage,
					$this->adjust_thumbnail_options = new \Brickrouge\Widget\AdjustThumbnailOptions
				),

				'class' => 'adjust',
				'data-widget-constructor' => 'AdjustThumbnail'
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
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

namespace Brickrouge\Widget;

class AdjustThumbnail extends \Icybee\Modules\Images\AdjustThumbnail
{

}