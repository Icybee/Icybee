<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use ICanBoogie\Module;

use Brickrouge\Element;
use Brickrouge\Text;

/**
 * "Feed" editor.
 */
class FeedEditorElement extends Element implements EditorElement
{
	private $elements = array();

	public function __construct(array $attributes=array())
	{
		global $core;

		$constructors = array();
		$modules = $core->modules;

		foreach ($modules->descriptors as $module_id => $descriptor)
		{
			if ($module_id == 'contents' || !$modules->is_extending($module_id, 'contents'))
			{
				continue;
			}

			$constructors[$module_id] = $descriptor[Module::T_TITLE];
		}

		uasort($constructors, 'ICanBoogie\unaccent_compare_ci');

		parent::__construct
		(
			'div', $attributes + array
			(
				self::CHILDREN => array
				(
					$this->elements['constructor'] = new Element
					(
						'select', array
						(
							Element::LABEL => 'Module',
							Element::LABEL_POSITION => 'above',
							Element::REQUIRED => true,
							Element::OPTIONS => array(null => '<sélectionner un module>') + $constructors
						)
					),

					$this->elements['limit'] = new Text
					(
						array
						(
							Element::LABEL => "Nombre d'entrées dans le flux",
							Element::LABEL_POSITION => 'above',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 10,

							'size' => 4
						)
					),

					$this->elements['settings'] = new Element
					(
						Element::TYPE_CHECKBOX_GROUP, array
						(
							Element::LABEL => 'Options',
							Element::LABEL_POSITION => 'above',
							Element::OPTIONS => array
							(
								'is_with_author' => "Mentionner l'auteur",
								'is_with_category' => "Mentionner les catégories",
								'is_with_attached' => "Ajouter les pièces jointes"
							),

							'class' => 'list'
						)
					)
				),

				'class' => 'editor feed combo'
			)
		);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset == 'name')
		{
			foreach ($this->elements as $identifier => $element)
			{
				$element['name'] = $value . '[' . $identifier . ']';
			}
		}

		parent::offsetSet($offset, $value);
	}

	public function render_inner_html()
	{
		$value = $this['value'];

		if ($value)
		{
			if (is_string($value))
			{
				$value = json_decode($value, true);
			}

			foreach ($value as $identifier => $v)
			{
				$this->elements[$identifier]['value'] = $v;
			}
		}

		return parent::render_inner_html();
	}
}