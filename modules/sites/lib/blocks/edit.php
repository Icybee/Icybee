<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Sites;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

class EditBlock extends \Icybee\EditBlock
{
	protected function alter_attributes(array $attributes)
	{
		return wd_array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'location' => array
					(
						'title' => 'Emplacement',
						'class' => 'location'
					),

					'i18n' => array
					(
						'title' => 'Internationalisation'
					),

					'advanced' => array
					(
						'title' => 'Advanced parameters'
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$core->document->css->add('../../public/admin.css');

		$languages = $core->locale->conventions['localeDisplayNames']['languages'];

		asort($languages);

		$tz = ini_get('date.timezone');

		#

		$placeholder_tld = null;
		$placeholder_domain = null;
		$placeholder_subdomain = null;

		$parts = explode('.', $_SERVER['HTTP_HOST']);
		$parts = array_reverse($parts);

		if (!$properties['tld'] && isset($parts[0]))
		{
			$placeholder_tld = $parts[0];
		}

		if (!$properties['domain'] && isset($parts[1]))
		{
			$placeholder_domain = $parts[1];
		}

		if (!$properties['subdomain'] && isset($parts[2]))
		{
			$placeholder_subdomain = $parts[2];
		}

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				'title' => new Text
				(
					array
					(
						Form::LABEL => 'Title',
						Element::REQUIRED => true
					)
				),

				'admin_title' => new Text
				(
					array
					(
						Form::LABEL => 'Admin title',
						Element::DESCRIPTION => "Il s'agit du titre utilisé par l'interface d'administration."
					)
				),

				'email' => new Text
				(
					array
					(
						Form::LABEL => 'Email',
						Element::REQUIRED => true,
						Element::VALIDATOR => array('Brickrouge\Form::validate_email'),
						Element::DESCRIPTION => "The site's email is usually used as default sender email,
						but can also be used as a contact address."
					)
				),

				'subdomain' => new Text
				(
					array
					(
						Form::LABEL => 'Sous-domaine',
						Element::GROUP => 'location',

						'size' => 16,
						'placeholder' => $placeholder_subdomain
					)
				),

				'domain' => new Text
				(
					array
					(
						Form::LABEL => 'Domaine',
						Text::ADDON => '.',
						Text::ADDON_POSITION => 'before',
						Element::GROUP => 'location',

						'placeholder' => $placeholder_domain
					)
				),

				'tld' => new Text
				(
					array
					(
						Form::LABEL => 'TLD',
						Text::ADDON => '.',
						Text::ADDON_POSITION => 'before',
						Element::GROUP => 'location',

						'size' => 8,
						'placeholder' => $placeholder_tld
					)
				),

				'path' => new Text
				(
					array
					(
						Form::LABEL => 'Chemin',
						Text::ADDON => '/',
						Text::ADDON_POSITION => 'before',
						Element::GROUP => 'location',

						'value' => trim($properties['path'], '/')
					)
				),

				'language' => new Element
				(
					'select', array
					(
						Form::LABEL => 'Langue',
						Element::REQUIRED => true,
						Element::GROUP => 'i18n',
						Element::OPTIONS => array(null => '') + $languages
					)
				),

				'nativeid' =>  $this->get_control_translation_sources($properties, $attributes),

				'timezone' => new Widget\TimeZone
				(
					array
					(
						Form::LABEL => 'Fuseau horaire',
						Element::GROUP => 'i18n',
						Element::DESCRIPTION => "Par défaut, le fuseau horaire du serveur est
						utilisé (actuellement&nbsp;: <q>" . ($tz ? $tz : 'non défini') . "</q>)."
					)
				),

				'status' => new Element
				(
					'select', array
					(
						Form::LABEL => 'Status',
						Element::GROUP => 'advanced',
						Element::OPTIONS => array
						(
							0 => 'Le site est hors ligne',
							1 => 'Le site est en ligne',
							2 => 'Le site est en travaux',
							3 => "Le site est interdit d'accès"
						)
					)
				),

				'model' => new Element
				(
					'select', array
					(
						Form::LABEL => 'Modèle',
						Element::GROUP => 'advanced',
						Element::OPTIONS => array(null => '<défaut>') + $this->get_site_models()
					)
				)
			)
		);
	}

	private function get_site_models()
	{
		$models = array();

		$dh = opendir(\ICanBoogie\DOCUMENT_ROOT . 'protected');

		while ($file = readdir($dh))
		{
			if ($file[0] == '.' || $file == 'all' || $file == 'default')
			{
				continue;
			}

			$models[] = $file;
		}

		if (!$models)
		{
			return $models;
		}

		sort($models);

		return array_combine($models, $models);
	}

	protected function get_control_translation_sources(array &$properties, array &$attributes)
	{
		$options = $this->module->model
		->select('siteid, concat(title, ":", language) title')
		->where('siteid != ?', (int) $properties['siteid'])
		->pairs;

		if (!$options)
		{
			return;
		}

		return new Element
		(
			'select', array
			(
				Form::LABEL => 'Traduction source',
				Element::GROUP => 'i18n',
				Element::OPTIONS => array(0 => '<none>') + $options
			)
		);
	}
}