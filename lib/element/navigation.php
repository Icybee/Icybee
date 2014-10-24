<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\PropertyNotDefined;

use Brickrouge\A;
use Brickrouge\DropdownMenu;

use Icybee\Modules\Users\Users;

/**
 * Admin navigation bar.
 */
class Navigation extends \Brickrouge\Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'div', $attributes + array
			(
				'class' => 'navbar'
			)
		);
	}

	protected function render_inner_html()
	{
		global $core;

		$rc = parent::render_inner_html();

		$links = array();
		$routes = $core->routes;
		$user = $core->user;
		$menus = array();

		$modules = $core->modules;
		$descriptors = $modules->descriptors;

		foreach ($routes as $route)
		{
			if (empty($route['index']) || empty($route['module']))
			{
				continue;
			}

			$module_id = $route['module'];

			if (!isset($modules[$module_id]))
			{
				continue;
			}

			$category = $descriptors[$module_id][Module::T_CATEGORY];

			$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$menus[$category][$route['pattern']] = $route;

			$links[$category] = I18n\t($category, array(), array('scope' => 'module_category')); // TODO: a same category is translated multiple time
		}

		uasort($links, 'ICanBoogie\unaccent_compare_ci');

		$links = array_merge
		(
			array
			(
				'dashboard' => 'Dashboard',
				'features' => 'Features'
			),

			$links
		);

		if (empty($menus['features']))
		{
			unset($links['features']);
		}

		$matching_route = null;

		try
		{
			$matching_route = $core->request->route;
		}
		catch (PropertyNotDefined $e) {}

		$active = $matching_route ? $descriptors[$matching_route->module][Module::T_CATEGORY] : 'dashboard';

		$rc .= '<ul class="nav">';

		foreach ($links as $path => $label)
		{
			if (strpos($active, $path) === 0)
			{
				$rc .= '<li class="active">';
			}
			else
			{
				$rc .= '<li>';
			}

			$url = \ICanBoogie\Routing\contextualize('/admin/'. $path);

			$rc .= '<a href="' . \ICanBoogie\escape($url) . '">' . $label . '</a>';

			if (isset($menus[$path]))
			{
				$rc .= $this->render_dropdown_menu($menus[$path]);
			}

			$rc .= '</li>';
		}

		$rc .= '</ul>';

		return $rc;
	}

	protected function render_dropdown_menu(array $routes)
	{
		global $core;

		$options = array();
		$descriptors = $core->modules->descriptors;

		foreach ($routes as $route)
		{
			$title = $route['title'];
			$module_id = $route['module'];
			$module_flat_id = strtr($module_id, '.', '_');
			$title = I18n\t($module_flat_id, array(), array('scope' => 'module_title', 'default' => $descriptors[$module_id][Module::T_TITLE]));
			$url = \ICanBoogie\Routing\contextualize($route['pattern']);
			$options[$url] = array($title, $url);
		}

		uasort($options, function($a, $b) {

			return \ICanBoogie\unaccent_compare_ci($a[0], $b[0]);

		});

		array_walk($options, function(&$v) {

			list($title, $url) = $v;

			$v = new A($title, $url);

		});

		return new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $core->request->path
			)
		);
	}
}