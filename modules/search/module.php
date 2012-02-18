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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \Icybee\Module
{
	protected function __get_views()
	{
		return array
		(
			'home' => array
			(
				'title' => 'Rechercher sur le site',
				'renders' => \Icybee\Views\View::RENDERS_MANY
			)
		);
	}

	protected function block_config()
	{
		global $core;

		$core->document->css->add('public/admin.css');
		$core->document->js->add('public/admin.js');

		$options = array();

		foreach ($core->modules->descriptors as $module_id => $descriptor)
		{
			if (!isset($core->modules[$module_id]))
			{
				continue;
			}

			if (!self::is_extending($module_id, 'contents') && !self::is_extending($module_id, 'pages'))
			{
				continue;
			}

			$options[$module_id] = t($descriptor[self::T_TITLE]);
		}

		$options['google'] = '<em>Google</em>';

		asort($options);

		$scope = explode(',', $core->site->metas[$this->flat_id . '.scope']);
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

					'name' => "local[$this->flat_id.scope][$module_id]",
					'type' => 'checkbox',
					'checked' => !empty($scope[$module_id])
				)
			);

			$el .= '</li>';
		}

		$el .= '</ul>';

		#
		# description
		#

		$page = $core->site->resolve_view_target('search/home');

		if ($page)
		{
			$description_link = '<a href="' . $core->site->path . '/admin/pages/' . $page->nid . '/edit">' . wd_entities($page->title) . '</a>';
		}
		else
		{
			$description_link = '<q><a href="' . $core->site->path . '/admin/pages">Pages</a></q>';
		}

		return array
		(
			Element::GROUPS => array
			(
				'primary' => array
				(
					'description' => t($page ? 'description' : 'description_nopage', array(':link' => $description_link))
				)
			),

			Element::CHILDREN => array
			(
				"local[$this->flat_id.scope]" => new Element
				(
					'div', array
					(
						Form::LABEL => 'scope',
						Element::INNER_HTML => $el,
						Element::DESCRIPTION => 'scope'
					)
				),

				"local[$this->flat_id.limits.home]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_home',
						Element::DEFAULT_VALUE => 5
					)
				),

				"local[$this->flat_id.limits.list]" => new Text
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
}