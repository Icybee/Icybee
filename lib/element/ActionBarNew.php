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
use ICanBoogie\Routing\Routes;
use Icybee\Modules\Users\User;

/**
 * Action bar _new_ button.
 *
 * @property ModuleCollection $modules
 * @property Request $request
 * @property Routes $routes
 * @property User $user
 */
class ActionbarNew extends SplitButton
{
	const PATTERN = '#abn-pattern';
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

	public function __construct($label, array $attributes = [])
	{
		$options = $this->collect_routes();

		parent::__construct
		(
			$label, $attributes + array
			(
				self::OPTIONS => $options
			)
		);

		$route = $this[self::ROUTE];

		if ($route->pattern == $this[self::PATTERN])
		{
			$this->add_class('btn-info');
		}
		else
		{
			$this->add_class('btn-danger');
		}
	}

	private $render_as_button = false;

	protected function render_splitbutton_label($label, $class)
	{
		if ($this->render_as_button)
		{
			return '';
		}

		return new A($label, \ICanBoogie\Routing\contextualize($this[self::PATTERN]), [ 'class' => 'btn ' . $class ]);
	}

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

	public function render()
	{
		$route = $this->request->context->route;
		$module_id = $route->module;
		$match = $this->routes->find("/admin/$module_id/new");

		$this->render_as_button = !$match;

		if ($route->pattern != '/admin/dashboard' && !$match)
		{
			return '';
		}

		return parent::render();
	}

	protected function collect_routes()
	{
		$collection = [];
		$translations = [];

		$routes = $this->routes;
		$modules = $this->modules;
		$descriptors = $modules->descriptors;
		$user = $this->user;

		foreach ($routes as $route)
		{
			$pattern = $route['pattern'];

			if (!preg_match('#/new$#', $pattern))
			{
				continue;
			}

			$module_id = $route['module'];

			if (!isset($modules[$module_id]) || !$user->has_permission(Module::PERMISSION_CREATE, $module_id))
			{
				continue;
			}

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
