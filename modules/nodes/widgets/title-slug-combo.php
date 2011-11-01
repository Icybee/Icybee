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

use BrickRouge;
use BrickRouge\Element;

class TitleSlugCombo extends \BrickRouge\Widget
{
	const T_NODEID = '#node-id';
	const T_SLUG_NAME = '#slug-name';

	private $title_el;
	private $slug_tease;
	private $slug_el;

	public function __construct($tags=array(), $dummy=null)
	{
		$slugname = isset($tags[self::T_SLUG_NAME]) ? $tags[self::T_SLUG_NAME] : null;
		$label = isset($tags[Element::T_LABEL]) ? $tags[Element::T_LABEL] : null;
		$label_position = isset($tags[Element::T_LABEL_POSITION]) ? $tags[Element::T_LABEL_POSITION] : 'before';

		parent::__construct
		(
			'div', $tags + array
			(
				Element::T_CHILDREN => array
				(
					$this->title_el = new BrickRouge\Text
					(
						array
						(
							Element::T_LABEL_POSITION => $label_position,
							Element::T_REQUIRED => true
						)
					),

					$this->slug_tease = new Element
					(
						'span', array
						(
							self::T_INNER_HTML => '&nbsp;',

							'class' => 'slug-reminder small'
						)
					),

					'<a href="#slug-collapse" class="small">' . t('fold', array(), array('scope' => array('titleslugcombo', 'element'))) . '</a>',

					'<div class="slug">',

					$this->slug_el = new BrickRouge\Text
					(
						array
						(
							Element::T_LABEL => '.slug',
							Element::T_LABEL_POSITION => 'above',
							Element::T_GROUP => 'node',
							Element::T_DESCRIPTION => '.slug',

							'name' => $slugname
						)
					),

					'</div>'
				),

				Element::T_DATASET => array
				(
					'auto-label' => '<em>' . t('auto', array(), array('scope' => array('titleslugcombo', 'element'))) . '</em>'
				)
			)
		);
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('title-slug-combo.css');
		$document->js->add('title-slug-combo.js');
	}

	public function set($name, $value=null)
	{
		if ($name == 'name')
		{
			$this->title_el->set('name', $value);

			if (!$this->slug_el->get('name'))
			{
				$this->slug_el->set('name', $value . 'slug');
			}
		}

		parent::set($name, $value);
	}

	public function render_inner_html()
	{
		global $core;

		$slug = $this->slug_el->get('value');

		$tease = '<strong>Slug&nbsp;:</strong> ';
		$tease .= '<a href="#slug-edit" title="' . t('edit', array(), array('scope' => array('titleslugcombo', 'element'))) . '">' . ($slug ? wd_entities(wd_shorten($slug)) : $this->dataset['auto-label']) . '</a>';
		$tease .= ' <span>&ndash; <a href="slug-delete" class="warn">' . t('reset', array(), array('scope' => array('titleslugcombo', 'element'))) . '</a></span>';

		$this->slug_tease->innerHTML = $tease;

		$rc = parent::render_inner_html();

		$nid = $this->get(self::T_NODEID);

		if ($nid)
		{
			$node = $core->models['nodes'][$nid];

			if ($node && $node->url && $node->url[0] != '#')
			{
				$url = $node->url;
				$url_label = wd_shorten($url, 64);

				$rc .= '<p class="small light">';
				$rc .= '<strong>URL&nbsp;:</strong> ' . $url_label;
			}
		}

		return $rc;
	}
}