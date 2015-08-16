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

use Brickrouge\A;
use Brickrouge\SplitButton;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Routing\RouteCollection;
use Icybee\Binding\ObjectBindings;
use Icybee\Modules\Users\User;
use Icybee\Routing\CreateRouteFilter;

/**
 * Action bar _new_ button.
 *
 * @property ModuleCollection $modules
 * @property Request $request
 * @property RouteCollection $routes
 * @property User $user
 */
class ActionbarNew extends SplitButton
{
	use ObjectBindings;

	const ID = '#abn-id';
	const ROUTE = '#abn-route';

	protected function lazy_get_modules()
	{
		return $this->app->modules;
	}

	protected function lazy_get_request()
	{
		return $this->app->request;
	}

	protected function lazy_get_routes()
	{
		return $this->app->routes;
	}

	protected function lazy_get_user()
	{
		return $this->app->user;
	}

	/**
	 * @inheritdoc
	 */
	public function __construct($label, array $attributes = [])
	{
		$options = $this->collect_routes();

		parent::__construct($label, $attributes + [

			self::OPTIONS => $options

		]);

		$route = $this[self::ROUTE];

		$this->add_class($route->id === $this[self::ID] ? 'btn-info' : 'btn-danger');
	}

	private $render_as_button = false;

	/**
	 * @inheritdoc
	 */
	protected function render_splitbutton_label($label, $class)
	{
		if ($this->render_as_button)
		{
			return '';
		}

		return new A($label, $this->app->url_for($this[self::ID]), [ 'class' => 'btn ' . $class ]);
	}

	/**
	 * @inheritdoc
	 */
	protected function render_splitbutton_toggle($class)
	{
		if ($this->render_as_button)
		{
			return <<<EOT
<a href="javascript:void()" class="btn dropdown-toggle $class" data-toggle="dropdown">$this->inner_html <span class="caret"></span></a>
EOT;
		}

		return parent::render_splitbutton_toggle($class);
	}

	/**
	 * @inheritdoc
	 */
	public function render()
	{
		$route = $this->request->context->route;
		$module_id = $route->module;
		$has_create = isset($this->routes["admin:$module_id:create"]);

		$this->render_as_button = !$has_create;

		if ($route->id != 'admin:dashboard:index' && !$has_create)
		{
			return '';
		}

		return parent::render();
	}

	protected function collect_routes()
	{
		$collection = [];
		$translations = [];

		$routes = $this->routes->filter(new CreateRouteFilter($this->modules, $this->user));

		$modules = $this->modules;
		$descriptors = $modules->descriptors;

		foreach ($routes as $route)
		{
			$pattern = $route['pattern'];
			$module_id = $route['module'];

			$collection[$pattern] = $module_id;

			$flat_id = strtr($module_id, '.', '_');

			$translations[$module_id] = $this->t($flat_id . '.name', [ ':count' => 1 ], [

				'default' => \ICanBoogie\singularize($this->t("module_title.$flat_id", [], [

					'default' => $descriptors[$module_id][Descriptor::TITLE]

				]))
			]);
		}

		\ICanBoogie\stable_sort($collection, function($v) use ($translations) {

			return \ICanBoogie\downcase(\ICanBoogie\remove_accents($translations[$v]));

		});

		array_walk($collection, function(&$v, $k) use ($translations) {

			$label = $translations[$v];

			$v = new A($label, \ICanBoogie\Routing\contextualize($k));

		});

		return $collection;
	}
}
