<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget;

use ICanBoogie\Image;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class AdjustThumbnailVersion extends \Brickrouge\Group
{
	private $elements = array();

	public function __construct(array $attributes=array())
	{
		global $core;

		parent::__construct
		(
			\ICanBoogie\array_merge_recursive
			(
				array
				(
					Element::CHILDREN => array
					(
						new Element
						(
							'div', array
							(
								Form::LABEL => 'Dimensions',
								Element::CHILDREN => array
								(
									'w' => $this->elements['w'] = new Text
									(
										array
										(
											Text::ADDON => 'px',

											'class' => 'measure',
											'size' => 5
										)
									),

									' × ',

									'h' => $this->elements['h'] = new Text
									(
										array
										(
											Text::ADDON => 'px',

											'class' => 'measure',
											'size' => 5
										)
									)
								)
							)
						),

						'method' => $this->elements['method'] = new Element
						(
							'select', array
							(
								Form::LABEL => 'Méthode',
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
								Element::LABEL => 'Redimensionner mais ne pas agrandir'
							)
						),

						new Element
						(
							'div', array
							(
								Form::LABEL => 'Format de la miniature',

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

											'style' => 'display: inline-block; width: auto'
										)
									),

									'&nbsp;',

									'quality' => $this->elements['quality'] = new Text
									(
										array
										(
											Text::ADDON => 'Qualité',
											Text::ADDON_POSITION => 'before',
											self::DEFAULT_VALUE => 80,

											'class' => 'measure',
											'size' => 3
										)
									)
								)
							)
						),

						/*
						'interlace' => $this->elements['interlace'] = new Element
						(
							Element::TYPE_CHECKBOX, array
							(
								self::LABEL => 'Affichage progressif'
							)
						),
						*/

						'background' => $this->elements['background'] = new Text
						(
							array
							(
								Form::LABEL => 'Remplissage'
							)
						)
					),

					'class' => 'adjust widget-adjust-thumbnail-version'
				),

				$attributes
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('page.css');
		$document->js->add('adjust-thumbnail-version.js');
	}

	public function offsetSet($offset, $value)
	{
		switch ($offset)
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

					$element[$offset] = $v;
				}
			}
			break;

			case 'name':
			{
				foreach ($this->elements as $identifier => $element)
				{
					$element[$offset] = $value . '[' . $identifier . ']';
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

					$this->elements[$identifier][($identifier == 'interlace' || $identifier == 'no-upscale') ? 'checked' : 'value'] = $v;
				}
			}
			break;
		}

		parent::offsetSet($offset, $value);
	}
}