<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Comments;

use ICanBoogie\ActiveRecord\Comment;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class ConfigBlock extends \Icybee\ConfigBlock
{
	protected function alter_attributes(array $attributes)
	{
		global $core;

		// TODO-20101101: move this to operation `config`

		$ns = $this->module->flat_id;

		$keywords = $core->registry[$ns . '.spam.keywords'];
		$keywords = preg_split('#[\s,]+#', $keywords, 0, PREG_SPLIT_NO_EMPTY);

		sort($keywords);

		$keywords = implode(', ', $keywords);

		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Form::VALUES => array
				(
					"global[$ns.spam.keywords]" => $keywords // TODO-20120218: should be in alter_values
				),

				Element::GROUPS => array
				(
					'primary' => array
					(
						'title' => 'Général'
					),

					'response' => array
					(
						'title' => "Message de notification à l'auteur lors d'une réponse"
					),

					'spam' => array
					(
						'title' => 'Paramètres du filtre anti-spam',
						'description' => "Les paramètres du filtre anti-spam s'appliquent à tous les
						sites."
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$ns = $this->module->flat_id;

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				"local[$ns.form_id]" => new \WdFormSelectorElement
				(
					'select', array
					(
						Form::LABEL => 'Formulaire',
						Element::GROUP => 'primary',
						Element::REQUIRED => true,
						Element::DESCRIPTION => "Il s'agit du formulaire à utiliser pour la
						saisie des commentaires."
					)
				),

				"local[$ns.delay]" => new Text
				(
					array
					(
						Form::LABEL => 'Intervale entre deux commentaires',
						Text::ADDON => 'minutes',
						Element::DEFAULT_VALUE => 3,

						'size' => 3,
						'style' => 'text-align: right'
					)
				),

				"local[$ns.default_status]" => new Element
				(
					'select', array
					(
						Form::LABEL => 'Status par défaut',
						Element::OPTIONS => array
						(
							'pending' => 'Pending',
							'approved' => 'Approuvé'
						),
						Element::DESCRIPTION => "Il s'agit du status par défaut pour les nouveaux
						commentaires."
					)
				),

				"global[$ns.spam.urls]" => new Element
				(
					'textarea', array
					(
						Form::LABEL => 'URLs',
						Element::GROUP => 'spam',
						'rows' => 5
					)
				),

				"global[$ns.spam.keywords]" => new Element
				(
					'textarea', array
					(
						Form::LABEL => 'Mots clés',
						Element::GROUP => 'spam',
						'rows' => 5
					)
				)
			)
		);
	}
}