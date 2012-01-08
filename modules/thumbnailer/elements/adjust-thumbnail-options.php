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

use ICanBoogie\Image;
use BrickRouge\Element;
use BrickRouge\Text;

class AdjustThumbnailOptions extends Element
{
	protected $elements = array();

	public function __construct($tags, $dummy=null)
	{
		$versions = array(null => '<personnalisé>');

		parent::__construct
		(
			'div', wd_array_merge_recursive
			(
				array
				(
					self::CHILDREN => array
					(
						'v' => $this->elements['v'] = new Element
						(
							'select', array
							(
								Element::OPTIONS => $versions
							)
						),

						'w' => $this->elements['w'] = new Text
						(
							array
							(
								/*
								Element::LABEL => 'Dimensions',
								Element::LABEL_POSITION => 'before',
								*/
								'size' => 5
							)
						),

						'h' => $this->elements['h'] = new Text
						(
							array
							(
								'size' => 5
							)
						),

						'method' => $this->elements['method'] = new Element
						(
							'select', array
							(
								self::LABEL => 'Méthode',
								self::LABEL_POSITION => 'above',

								Element::OPTIONS => array
								(
									Image::RESIZE_FILL => 'Remplir',
									Image::RESIZE_FIT => 'Ajuster',
									Image::RESIZE_SURFACE => 'Surface',
									Image::RESIZE_FIXED_HEIGHT => 'Hauteur fixe, largeur ajustée',
									Image::RESIZE_FIXED_HEIGHT_CROPPED => 'Hauteur fixe, largeur respectée',
									Image::RESIZE_FIXED_WIDTH => 'Largeur fixe, hauteur ajustée',
									Image::RESIZE_FIXED_WIDTH_CROPPED => 'Largeur fixe, hauteur respectée',
									Image::RESIZE_CONSTRAINED => 'Contraindre'
								)
							)
						),

						'no-upscale' => $this->elements['no-upscale'] = new Element
						(
							Element::TYPE_CHECKBOX, array
							(
								Element::LABEL => 'Ne pas agrandir'
							)
						),

						'format' => $this->elements['format'] = new Element
						(
							'select', array
							(
								self::LABEL => 'Format',
								self::LABEL_POSITION => 'before',

								self::OPTIONS => array
								(
									'jpeg' => 'JPEG',
									'png' => 'PNG',
									'gif' => 'GIF'
								),

								self::DEFAULT_VALUE => 'jpeg',

								'style' => 'display: inline-block'
							)
						),

						'quality' => $this->elements['quality'] = new Text
						(
							array
							(
								self::LABEL => 'Qualité',
								self::LABEL_POSITION => 'before',
								self::DEFAULT_VALUE => 80,

								'size' => 3
							)
						),

						'interlace' => $this->elements['interlace'] = new Element
						(
							Element::TYPE_CHECKBOX, array
							(
								self::LABEL => 'Affichage progressif'
							)
						),

						'background' => $this->elements['background'] = new Text
						(
							array
							(
								self::LABEL => 'Remplissage',
								self::LABEL_POSITION => 'above'
							)
						),

						'lightbox' => $this->elements['lightbox'] = new Element
						(
							Element::TYPE_CHECKBOX, array
							(
								self::LABEL => "Afficher l'original en lightbox"
							)
						)
					),

					'class' => 'widget-adjust-thumbnail-options'
				),

				$tags
			)
		);
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('adjust-thumbnail-options.css');
		$document->js->add('adjust-thumbnail-options.js');
	}

	public function set($name, $value=null)
	{
		if (is_string($name))
		{
			switch ($name)
			{
				case self::DEFAULT_VALUE:
				{
					foreach ($this->elements as $identifier => $element)
					{
						if (!array_key_exists($identifier, $value))
						{
							continue;
						}

						$element->set($name, $value[$identifier]);
					}
				}
				break;

				case 'name':
				{
					foreach ($this->elements as $identifier => $element)
					{
						$element->set($name, $value . '[' . $identifier . ']');
					}
				}
				break;
			}
		}

		parent::set($name, $value);
	}

	protected function render_inner_html()
	{
		extract($this->elements);

		$no_upscale = $this->elements['no-upscale'];

		return <<<EOT
<div class="form-element">$w × $h <span class="label">px</span></div>
<div class="form-element">$method</div>
<div class="form-element">$background</div>
<div class="form-element">$format $quality</div>
<div class="form-element checkbox-group list">$no_upscale $interlace $lightbox</div>
EOT;
	}
}