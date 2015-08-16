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
use ICanBoogie\Routing\Route;
use ICanBoogie\Routing\RouteCollection;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

use Icybee\Binding\ObjectBindings;
use Icybee\Modules\Users\User;
use Icybee\Routing\ActionBarNavRouteFilter;

/**
 * Action bar navigation.
 *
 * @property Request $request
 * @property RouteCollection $routes
 * @property User $user
 */
class ActionBarNav extends Element
{
	use ObjectBindings;

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
	 * @return RouteCollection
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

		if (!$collection->count())
		{
			throw new ElementIsEmpty;
		}

		$html = '';

		foreach ($collection as $id => $definition)
		{
			$html .= $this->render_link($collection[$id], $current_route);
		}

		return $html . parent::render_inner_html();
	}

	protected function render_link(Route $route, Route $current_route)
	{
		$title = $route->id;

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

	/**
	 * @param Route $current_route
	 *
	 * @return RouteCollection
	 *
	 * @throws ElementIsEmpty
	 */
	protected function collect_routes(Route $current_route)
	{
		if (empty($current_route->module))
		{
			throw new ElementIsEmpty;
		}

		return $this->routes->filter(new ActionBarNavRouteFilter($current_route, $this->user));
	}
}
