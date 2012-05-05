<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Page;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class EditBlock extends \ICanBoogie\Modules\Nodes\EditBlock
{
	protected function alter_attributes(array $attributes)
	{
		global $core;

		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Form::HIDDENS => array
				(
					Page::SITEID => $core->site_id,
					Page::LANGUAGE => $core->site->language
				),

				Element::GROUPS => array
				(
					'advanced' => array
					(
						'title' => 'Advanced',
						'weight' => 30
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$core->document->css->add('../../public/edit.css');
		$core->document->js->add('../../public/edit.js');

		$nid = $properties[Node::NID];
		$is_alone = !$this->module->model->select('nid')->where(array('siteid' => $core->site_id))->rc;

		list($contents_tags, $template_info) = $this->module->get_contents_section($properties[Node::NID], $properties[Page::TEMPLATE]);

		#
		# parentid
		#

		$parentid_el = null;

		if (!$is_alone)
		{
			$parentid_el = new \WdPageSelectorElement
			(
				'select', array
				(
					Form::LABEL => 'parentid',
					Element::OPTIONS_DISABLED => $nid ? array($nid => true) : null,
					Element::DESCRIPTION => 'parentid'
				)
			);
		}

		#
		# location element
		#

		$location_el = null;

		if (!$is_alone)
		{
			$location_el = new \WdPageSelectorElement
			(
				'select', array
				(
					Form::LABEL => 'location',
					Element::GROUP => 'advanced',
					Element::WEIGHT => 10,
					Element::OPTIONS_DISABLED => $nid ? array($nid => true) : null,
					Element::DESCRIPTION => 'location'
				)
			);
		}

		$contents_children = array();

		if (isset($contents_tags[Element::CHILDREN]))
		{
			$contents_children = $contents_tags[Element::CHILDREN];

			unset($contents_tags[Element::CHILDREN]);

			$attributes = \ICanBoogie\array_merge_recursive($attributes, $contents_tags);
		}

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Page::PARENTID => $parentid_el,
				Page::SITEID => null,

				Page::IS_NAVIGATION_EXCLUDED => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'is_navigation_excluded',
						Element::GROUP => 'visibility',
						Element::DESCRIPTION => 'is_navigation_excluded'
					)
				),

				Page::LABEL => new Text
				(
					array
					(
						Form::LABEL => 'label',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => 'label'
					)
				),

				Page::PATTERN => new Text
				(
					array
					(
						Form::LABEL => 'pattern',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => 'pattern'
					)
				),

				Page::LOCATIONID => $location_el
			),

			$contents_children
		);
	}
}