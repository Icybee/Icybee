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
use ICanBoogie\I18n\Translator;
use ICanBoogie\Module;
use ICanBoogie\Routing\Route;
use ICanBoogie\Routing\RouteCollection;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

use Icybee\Binding\PrototypedBindings;
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
	use PrototypedBindings;

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

	/**
	 * @inheritdoc
	 *
	 * @throws ElementIsEmpty if there is no link to render.
	 */
	protected function render_inner_html()
	{
		$current_route = $this->request->context->route;

		if (empty($current_route->module))
		{
			throw new ElementIsEmpty;
		}

		$collection = $this->collect_routes($current_route);

		$html = '';

		foreach ($collection as $id => $definition)
		{
			$html .= $this->render_link($collection[$id], $current_route);
		}

		if (!preg_match('/\:index$/', $current_route->id) && !isset($collection[$current_route->id]))
		{
			$html .= $this->render_link($current_route, $current_route);
		}

		if (!$html)
		{
			throw new ElementIsEmpty;
		}

		return $html . parent::render_inner_html();
	}

	/**
	 * Renders navigation link.
	 *
	 * @param Route $route
	 * @param Route $current_route
	 *
	 * @return A
	 */
	protected function render_link(Route $route, Route $current_route)
	{
		$title = $this->t($route->id, [], [

			'scope' => 'route.title',
			'default' => function(Translator $t, $str) {

				$parts = explode(':', $str);

				return $t(array_pop($parts), [], [ 'scope' => 'route.title' ]);

			}

		]);

		$formatted_route = $route->format($this->request->path_params);

		$link = new A($title, $formatted_route->url, [ 'class' => 'actionbar-link' ]);

		if ($route->id == $current_route->id)
		{
			$link->add_class('active');
		}

		return $link;
	}

	/**
	 * Collect routes.
	 *
	 * @param Route $current_route
	 *
	 * @return RouteCollection
	 */
	protected function collect_routes(Route $current_route)
	{
		return $this->routes->filter(new ActionBarNavRouteFilter($current_route, $this->user));
	}
}
