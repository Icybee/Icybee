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

class AdjustThumbnailOptions extends Element
{
	protected $elements = array();

	public function __construct($tags, $dummy=null)
	{
		global $document;

		$versions = array(null => '<personnalisé>');

		parent::__construct
		(
			'div', wd_array_merge_recursive
			(
				array
				(
					self::T_CHILDREN => array
					(
						'v' => $this->elements['v'] = new Element
						(
							'select', array
							(
								Element::T_OPTIONS => $versions
							)
						),

						'w' => $this->elements['w'] = new Element
						(
							Element::E_TEXT, array
							(
								/*
								Element::T_LABEL => 'Dimensions',
								Element::T_LABEL_POSITION => 'before',
								*/
								'size' => 5
							)
						),

						'h' => $this->elements['h'] = new Element
						(
							Element::E_TEXT, array
							(
								'size' => 5
							)
						),

						'method' => $this->elements['method'] = new Element
						(
							'select', array
							(
								self::T_LABEL => 'Méthode',
								self::T_LABEL_POSITION => 'above',

								Element::T_OPTIONS => array
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
							Element::E_CHECKBOX, array
							(
								Element::T_LABEL => 'Ne pas agrandir'
							)
						),

						'format' => $this->elements['format'] = new Element
						(
							'select', array
							(
								self::T_LABEL => 'Format',
								self::T_LABEL_POSITION => 'before',

								self::T_OPTIONS => array
								(
									'jpeg' => 'JPEG',
									'png' => 'PNG',
									'gif' => 'GIF'
								),

								self::T_DEFAULT => 'jpeg',

								'style' => 'display: inline-block'
							)
						),

						'quality' => $this->elements['quality'] = new Element
						(
							Element::E_TEXT, array
							(
								self::T_LABEL => 'Qualité',
								self::T_LABEL_POSITION => 'before',
								self::T_DEFAULT => 80,

								'size' => 3
							)
						),

						'interlace' => $this->elements['interlace'] = new Element
						(
							Element::E_CHECKBOX, array
							(
								self::T_LABEL => 'Affichage progressif'
							)
						),

						'background' => $this->elements['background'] = new Element
						(
							Element::E_TEXT, array
							(
								self::T_LABEL => 'Remplissage',
								self::T_LABEL_POSITION => 'above'
							)
						),

						'lightbox' => $this->elements['lightbox'] = new Element
						(
							Element::E_CHECKBOX, array
							(
								self::T_LABEL => "Afficher l'original en lightbox"
							)
						)
					),

					'class' => 'widget-adjust-thumbnail-options'
				),

				$tags
			)
		);

		$document->css->add('adjust-thumbnail-options.css');
		$document->js->add('adjust-thumbnail-options.js');
	}

	public function set($name, $value=null)
	{
		if (is_string($name))
		{
			switch ($name)
			{
				case self::T_DEFAULT:
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