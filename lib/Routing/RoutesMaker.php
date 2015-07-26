<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Routing;

use ICanBoogie\HTTP\Request;

class RoutesMaker extends \ICanBoogie\Routing\RoutesMaker
{
	static public function admin($module_id, $controller, array $options = [])
	{
		$options = static::normalize_options($options);
		$actions = static::filter_actions(static::get_admin_actions(), $options);

		$routes = [];

		foreach (static::routes($module_id, $controller, $actions, $options) as $id => $route)
		{
			$as = 'admin:' . $route['as'];

			$route['pattern'] = '/admin' . $route['pattern'];
			$route['as'] = $as;
			$route['module'] = $module_id;

			$routes[$as] = $route;
		}

		return $routes;
	}

	static protected function get_admin_actions()
	{
		return static::get_resource_actions() + [

			'config' => [ '/{resource}/config', Request::METHOD_GET ]

		];
	}
}
