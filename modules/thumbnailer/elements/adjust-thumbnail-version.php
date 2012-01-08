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

class AdjustThumbnailVersion extends \BrickRouge\Widget
{
	private $elements = array();

	public function __construct($tags, $dummy=null)
	{
		global $core;

		parent::__construct
		(
			'div', wd_array_merge_recursive
			(
				array
				(
					Element::CHILDREN => array
					(
						new Element
						(
							'div', array
							(
								Element::LABEL => 'Dimensions',
								Element::LABEL_POSITION => 'above',
								Element::CHILDREN => array
								(
									'w' => $this->elements['w'] = new Text
									(
										array
										(
											'size' => 5
										)
									),

									' × ',

									'h' => $this->elements['h'] = new Text
									(
										array
										(
											'size' => 5
										)
									),

									'&nbsp;px',
								)
							)
						),

						'method' => $this->elements['method'] = new Element
						(
							'select', array
							(
								Element::LABEL => 'Méthode',
								Element::LABEL_POSITION => 'above',
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
								Element::LABEL => 'Redimensionner, mais ne pas agrandir'
							)
						),

						new Element
						(
							'div', array
							(
								self::LABEL => 'Format de la miniature',
								self::LABEL_POSITION => 'above',
								self::CHILDREN => array
								(
									'format' => $this->elements['format'] = new Element
									(
										'select', array
										(
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

									'&nbsp;',

									'quality' => $this->elements['quality'] = new Text
									(
										array
										(
											self::LABEL => 'Qualité',
											self::LABEL_POSITION => 'before',
											self::DEFAULT_VALUE => 80,

											'size' => 3
										)
									)
								)
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
						)
					),

					'class' => 'adjust'
				),

				$tags
			)
		);
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('page.css');
		$document->js->add('adjust-thumbnail-version.js');
	}

	public function set($name, $value=null)
	{
		if (is_string($name))
		{
			switch ($name)
			{
				case self::DEFAULT_VALUE:
				{
					$options = $value;

					if (is_string($options))
					{
						$options = json_decode($options);
					}

					foreach ($options as $identifier => $v)
					{
						if (empty($this->elements[$identifier]))
						{
							continue;
						}

						$element->set($name, $v);
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

				case 'value':
				{
					$options = $value;

					if (is_string($options))
					{
						$options = json_decode($options);
					}

					if (!$options)
					{
						break;
					}

					foreach ($options as $identifier => $v)
					{
						if (empty($this->elements[$identifier]))
						{
							continue;
						}

						// FIXME-20110518: use handle_value() ?

						$this->elements[$identifier]->set(($identifier == 'interlace' || $identifier == 'no-upscale') ? 'checked' : 'value', $v);
					}
				}
				break;
			}
		}

		parent::set($name, $value);
	}
}