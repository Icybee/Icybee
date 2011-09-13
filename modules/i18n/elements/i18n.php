<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Node;
use BrickRouge\Element;

class WdI18nElement extends Element
{
	const T_CONSTRUCTOR = '#i18n-constructor';

	private $el_language;
	private $el_native_nid;

	public function __construct($tags, $dummy=null)
	{
		global $core;

		$languages = $core->models['sites']->count('language');

		foreach ($languages as $language => $dummy)
		{
			$languages[$language] = $core->locale->conventions['localeDisplayNames']['languages'][$language];
		}

		parent::__construct
		(
			'div', $tags + array
			(
				Element::T_CHILDREN => array
				(
					Node::LANGUAGE => $this->el_language = new Element
					(
						'select', array
						(
							Element::T_LABEL => '.language',
							Element::T_LABEL_POSITION => 'before',
							Element::T_OPTIONS => array
							(
								null => '.neutral'
							)

							+ $languages,

							Element::T_DESCRIPTION => '.language'
						)
					),

					// TODO-20110206: Use the WdI18nLinkElement element

					Node::NATIVEID => $this->el_native_nid = new Element
					(
						'em', array
						(
							Element::T_LABEL => '.nativeid',
							Element::T_LABEL_POSITION => 'before',
							Element::T_INNER_HTML => "Il n'y a pas d'entrée à traduire.",

							'class' => 'small'
						)
					)
				),

				'class' => 'wd-i18n'
			)
		);
	}

	protected function render_inner_html()
	{
		global $core, $document;

		$document->js->add('i18n.js');

		$site = $core->site;
		$native = $site->native->language;
		$language = $this->el_language->get('value');
		$sources = null;
		$source_el = null;

		$this->dataset['native'] = $native;

		if (!$language || ($language != $native))
		{
			$constructor = $this->get(self::T_CONSTRUCTOR);

			if ($constructor == 'pages')
			{
				$nodes = $core->models['pages']->select('nid, parentid, title')->where('language = ?', $native)
				->order('weight, created')->all(PDO::FETCH_OBJ);

				$tree = Model\Pages::nestNodes($nodes);

				if ($tree)
				{
					Model\Pages::setNodesDepth($tree);
					$entries = Model\Pages::levelNodesById($tree);

					foreach ($entries as $entry)
					{
						$sources[$entry->nid] = str_repeat("\xC2\xA0", $entry->depth * 4) . $entry->title;
					}
				}
			}
			else
			{
				$sources = $core->models['nodes']->select('nid, title')
				->where('constructor = ? AND language = ?', $constructor, $native)->order('title')
				->pairs;

				foreach ($sources as &$label)
				{
					$label = wd_shorten($label);
				}

				unset($label);
			}
		}

		if ($sources)
		{
			$native_nid = $this->el_native_nid->get('value');

			$this->children[Node::NATIVEID] = new Element
			(
				'select', array
				(
					Element::T_LABEL => '.nativeid',
					Element::T_LABEL_POSITION => 'before',
					Element::T_GROUP => 'i18n',
					Element::T_OPTIONS => array
					(
						null => ''
					)

					+ $sources,

					Element::T_DESCRIPTION => t('nativeid', array(':native' => $native, ':language' => $site->language), array('scope' => array('element', 'description'))),

					'name' => Node::NATIVEID,
					'value' => $native_nid
				)
			);
		}

		return parent::render_inner_html();
	}

	static public function operation_nodes_language(WdOperation $operation)
	{
		global $core;

		$nid = $operation->params['nid'];

		return $core->models['nodes']->select('language')->where(array('nid' => $nid))->rc;
	}
}