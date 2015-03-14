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

use ICanBoogie\Module;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\PropertyNotDefined;

use Brickrouge\A;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

/**
 * Admin navigation bar.
 *
 * @property \ICanBoogie\Core $app
 * @property \ICanBoogie\Module\ModuleCollection $modules
 * @property \ICanBoogie\HTTP\Request $request
 * @property \ICanBoogie\Routing\Route $route
 * @property \ICanBoogie\Routing\Routes $routes
 * @property \Icybee\Modules\Users\User $user
 */
class Navigation extends Element
{
	protected function lazy_get_modules()
	{
		return $this->app->modules;
	}

	protected function lazy_get_request()
	{
		return $this->app->request;
	}

	protected function lazy_get_route()
	{
		return $this->request->context->route;
	}

	protected function lazy_get_routes()
	{
		return $this->app->routes;
	}

	protected function lazy_get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [

			'class' => 'navbar'

		]);
	}

	protected function render_inner_html()
	{
		$rc = parent::render_inner_html();

		$links = [];
		$routes = $this->routes;
		$user = $this->user;
		$menus = [];

		$modules = $this->modules;
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

			$category = $descriptors[$module_id][Descriptor::CATEGORY];

			$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$menus[$category][$route['pattern']] = $route;

			$links[$category] = $this->t($category, [], [ 'scope' => 'module_category' ]); // TODO: a same category is translated multiple time
		}

		uasort($links, 'ICanBoogie\unaccent_compare_ci');

		$links = array_merge([

			'dashboard' => 'Dashboard',
			'features' => 'Features'

		], $links);

		if (empty($menus['features']))
		{
			unset($links['features']);
		}

		$matching_route = null;

		try
		{
			$matching_route = $this->route;
		}
		catch (PropertyNotDefined $e) {}

		$active = null;

		if (isset($matching_route->module))
		{
			$active = $matching_route ? $descriptors[$matching_route->module][Module\Descriptor::CATEGORY] : 'dashboard';
		}

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
		$options = [];
		$descriptors = $this->modules->descriptors;

		foreach ($routes as $route)
		{
			$module_id = $route['module'];
			$module_flat_id = strtr($module_id, '.', '_');
			$title = $this->t($module_flat_id, [], [ 'scope' => 'module_title', 'default' => $descriptors[$module_id][Descriptor::TITLE] ]);
			$url = \ICanBoogie\Routing\contextualize($route['pattern']);
			$options[$url] = [ $title, $url ];
		}

		uasort($options, function($a, $b) {

			return \ICanBoogie\unaccent_compare_ci($a[0], $b[0]);

		});

		array_walk($options, function(&$v) {

			list($title, $url) = $v;

			$v = new A($title, $url);

		});

		return new DropdownMenu([

			DropdownMenu::OPTIONS => $options,

			'value' => $this->request->path

		]);
	}
}
