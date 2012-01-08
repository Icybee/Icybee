<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;
use BrickRouge\Widget;
use Icybee\Manager;

// http://labs.apache.org/webarch/uri/rfc/rfc3986.html

class Sites extends \Icybee\Module
{
	public function update_cache()
	{
		global $core;

		$core->vars['sites'] = serialize($this->model->all);
	}

	protected function block_manage()
	{
		return new Manager\Sites
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'url', 'language', 'timezone', 'status')
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core, $document;

		$document->css->add('public/edit.css');

		$translation_sources_el = null;
		$translation_sources_options = $this->model
		->select('siteid, concat(title, ":", language) title')
		->where('siteid != ?', (int) $properties['siteid'])
		->pairs;

		$tz = ini_get('date.timezone');

		if ($translation_sources_options)
		{
			$translation_sources_el = new Element
			(
				'select', array
				(
					Form::LABEL => 'Source de traduction',
					Element::GROUP => 'i18n',
					Element::OPTIONS => array(0 => '<aucune>') + $translation_sources_options
				)
			);
		}

		$languages = $core->locale->conventions['localeDisplayNames']['languages'];

		asort($languages);

		$path = trim($properties['path'], '/');

		if ($path)
		{
			$path = '/' . $path;
		}

		return array
		(
			Element::GROUPS => array
			(
				'location' => array
				(
					'title' => 'Emplacement',
					'class' => 'form-section flat location'
				),

				'i18n' => array
				(
					'title' => 'Internationalisation',
					'class' => 'form-section flat'
				),

				'visibility' => array
				(
					'title' => 'Visibilité',
					'class' => 'form-section flat'
				)
			),

			Element::CHILDREN => array
			(
				'title' => new Text
				(
					array
					(
						Form::LABEL => 'Titre',
						Element::REQUIRED => true
					)
				),

				'admin_title' => new Text
				(
					array
					(
						Form::LABEL => 'Titre administratif',
						Element::DESCRIPTION => "Il s'agit du titre utilisé par l'interface d'administration."
					)
				),

				'model' => new Element
				(
					'select', array
					(
						Form::LABEL => 'Modèle',
						Element::OPTIONS => array
						(
							null => '<défaut>'
						)

						+ $this->get_site_models()
					)
				),

				'subdomain' => new Text
				(
					array
					(
						Form::LABEL => 'Sous-domaine',
						Element::GROUP => 'location',

						'size' => 16
					)
				),

				'domain' => new Text
				(
					array
					(
						Form::LABEL => 'Domaine',
						Element::GROUP => 'location'
					)
				),

				'tld' => new Text
				(
					array
					(
						Form::LABEL => 'TLD',
						Element::GROUP => 'location',

						'size' => 8
					)
				),

				'path' => new Text
				(
					array
					(
						Form::LABEL => 'Chemin',
						Element::GROUP => 'location',

						'value' => $path
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

				'nativeid' =>  $translation_sources_el,

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
						Element::OPTIONS => array
						(
							0 => 'Le site est hors ligne',
							1 => 'Le site est en ligne',
							2 => 'Le site est en travaux',
							3 => "Le site est interdit d'accès"
						)
					)
				)
			)
		);
	}

	private function get_site_models()
	{
		$models = array();

		$dh = opendir($_SERVER['DOCUMENT_ROOT'] . '/protected');

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
}