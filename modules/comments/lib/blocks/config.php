<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * Configuration block.
 */
class ConfigBlock extends \Icybee\ConfigBlock
{
	protected function get_attributes()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_attributes(), array
			(
				Element::GROUPS => array
				(
					'response' => array
					(
						'title' => "Message de notification à l'auteur lors d'une réponse"
					),

					'spam' => array
					(
						'title' => 'Paramètres anti-spam'
					)
				)
			)
		);
	}

	protected function get_children()
	{
		global $core;

		$ns = $this->module->flat_id;

		return array_merge
		(
			parent::get_children(), array
			(
				"local[$ns.form_id]" => new \Icybee\Modules\Forms\PopForm
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

				"local[$ns.delay]" => new Text
				(
					array
					(
						Form::LABEL => 'Intervale entre deux commentaires',
						Text::ADDON => 'minutes',
						Element::DEFAULT_VALUE => 3,
						Element::GROUP => 'spam',

						'size' => 3,
						'class' => 'measure'
					)
				)
			)
		);
	}
}