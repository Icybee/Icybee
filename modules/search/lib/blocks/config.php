<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Search;

use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to configure search.
 */
class ConfigBlock extends \Icybee\ConfigBlock
{
	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../../public/admin.css');
		$document->js->add('../../public/admin.js');
	}

	protected function alter_attributes(array $attributes)
	{
		global $core;

		$page = $core->site->resolve_view_target('search/home');

		if ($page)
		{
			$description_link = new A($page->title, Route::contextualize('/admin/pages/' . $page->nid . '/edit'));
		}
		else
		{
			$description_link = '<q>' . new A('Pages', Route::contextualize('/admin/pages')) . '</q>';
		}

		return wd_array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'primary' => array
					(
						'description' => t($page ? 'description' : 'description_nopage', array(':link' => $description_link))
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
				"local[$ns.scope]" => $this->get_control__scope($properties, $attributes),

				"local[$ns.limits.home]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_home',
						Element::DEFAULT_VALUE => 5
					)
				),

				"local[$ns.limits.list]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_list',
						Element::DEFAULT_VALUE => 10
					)
				)
			)
		);
	}

	protected function get_control__scope(array &$properties, array &$attributes)
	{
		global $core;

		$options = array();

		foreach ($core->modules->descriptors as $module_id => $descriptor)
		{
			if (!isset($core->modules[$module_id]))
			{
				continue;
			}

			if (!Module::is_extending($module_id, 'contents') && !Module::is_extending($module_id, 'pages'))
			{
				continue;
			}

			$options[$module_id] = t($descriptor[Module::T_TITLE]);
		}

		$options['google'] = '<em>Google</em>';

		asort($options);

		#

		$ns = $this->module->flat_id;

		$scope = explode(',', $core->site->metas[$ns . '.scope']);
		$scope = array_combine($scope, array_fill(0, count($scope), true));

		$sorted_options = array();

		foreach ($scope as $module_id => $dummy)
		{
			if (empty($options[$module_id]))
			{
				continue;
			}

			$sorted_options[$module_id] = $options[$module_id];
		}

		$sorted_options += $options;

		$el = '<ul class="sortable combo self-handle">';

		foreach ($sorted_options as $module_id => $label)
		{
			$el .= '<li>';
			$el .= new Element
			(
				'input', array
				(
					Element::LABEL => $label,

					'name' => "local[$ns.scope][$module_id]",
					'type' => 'checkbox',
					'checked' => !empty($scope[$module_id])
				)
			);

			$el .= '</li>';
		}

		$el .= '</ul>';

		return new Element
		(
			'div', array
			(
				Form::LABEL => 'scope',
				Element::INNER_HTML => $el,
				Element::DESCRIPTION => 'scope'
			)
		);
	}
}