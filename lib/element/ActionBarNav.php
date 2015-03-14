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

use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Routing\Pattern;
use ICanBoogie\Routing\Route;
use ICanBoogie\Routing\Routes;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

use Icybee\Modules\Users\User;

/**
 * Action bar navigation.
 *
 * @property Request $request
 * @property Routes $routes
 * @property User $user
 */
class ActionbarNav extends Element
{
	/**
	 * Returns application's request.
	 *
	 * @return Request
	 */
	protected function lazy_get_request()
	{
		return $this->app->request;
	}

	/**
	 * Returns application's routes.
	 *
	 * @return Routes
	 */
	protected function lazy_get_routes()
	{
		return $this->app->routes;
	}

	/**
	 * Returns application's user.
	 *
	 * @return User
	 */
	protected function lazy_get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-nav' ]);
	}

	protected function render_inner_html()
	{
		$current_route = $this->request->context->route;
		$collection = $this->collect_routes($current_route);

		if (empty($collection))
		{
			throw new ElementIsEmpty;
		}

		$html = '';

		foreach ($collection as $route)
		{
			$html .= $this->render_link($route, $current_route);
		}

		return $html . parent::render_inner_html();
	}

	protected function render_link(Route $route, Route $current_route)
	{
		$title = $route->title;

		if ($title{0} == '.') // TODO-20120214: COMPAT
		{
			$title = substr($title, 1);
		}

		$title = $this->t($title, [], [ 'scope' => 'block.title' ]);

		$formatted_route = $route->format($this->request->path_params);

		$link = new A($title, $formatted_route->url, [ 'class' => 'actionbar-link' ]);

		if ($route->pattern == $current_route->pattern)
		{
			$link->add_class('active');
		}

		return $link;
	}

	protected function collect_routes($current_route)
	{
		if (empty($current_route->module))
		{
			throw new ElementIsEmpty;
		}

		$collection = [];
		$pattern = $current_route->pattern;
		$module_id = $current_route->module;
		$user = $this->user;
		$index_pattern = "/admin/$module_id";
		$new_pattern = "/admin/$module_id/new";

		$skip = [

// 			"/admin/$module/config" => true,
			"/admin/$module_id/manage" => true

		];

		$routes = $this->routes;

		foreach ($routes as $route_id => $route_definition)
		{
			$route_module_id = isset($route_definition['module']) ? $route_definition['module'] : null;

			if (!$route_module_id || $route_module_id != $module_id || empty($route_definition['title']))
			{
				continue;
			}

			$r_pattern = $route_definition['pattern'];

			if ($r_pattern == $index_pattern || $r_pattern == $new_pattern)
			{
				continue;
			}

			if ($r_pattern == $pattern)
			{

			}
			else
			{
				if ((isset($route_definition['visibility']) && $route_definition['visibility'] == 'auto')
				|| Pattern::is_pattern($r_pattern)
				|| isset($skip[$r_pattern]))
				{
					continue;
				}
			}

			$permission = isset($route_definition['permission'])
				? $route_definition['permission']
				: Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$collection[$r_pattern] = $routes[$route_id];
		}

		return $collection;
	}
}
