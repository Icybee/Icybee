<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Admin\Element;

use Brickrouge\DropdownMenu;

use Brickrouge\A;

use ICanBoogie\ActiveRecord\Users;
use ICanBoogie\Module;
use ICanBoogie\Route;

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
		$routes = Route::routes();
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

			$links[$category] = t($category, array(), array('scope' => 'module_category')); // TODO: a same category is translated multiple time
		}

		uasort($links, 'wd_unaccent_compare_ci');

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

		$path = Route::decontextualize($core->request->path_info);
		$matching_route = Route::find($path, 'any', 'admin'); // FIXME-20120201: use the primary request object
		$selected = $matching_route ? $descriptors[$matching_route[0]['module']][Module::T_CATEGORY] : 'dashboard';

		$rc .= '<ul class="nav">';

		foreach ($links as $path => $label)
		{
			if (strpos($selected, $path) === 0)
			{
				$rc .= '<li class="selected">';
			}
			else
			{
				$rc .= '<li>';
			}

			$url = Route::contextualize('/admin/'. $path);

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
			$title = t($module_flat_id, array(), array('scope' => 'module_title', 'default' => $descriptors[$module_id][Module::T_TITLE]));
			$url = Route::contextualize($route['pattern']);
			$options[$url] = new A($title, $url);
		}

		return new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $core->request->path_info
			)
		);
	}
}