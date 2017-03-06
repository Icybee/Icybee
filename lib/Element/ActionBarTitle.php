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
use ICanBoogie\Routing\Route;

use Brickrouge\A;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

/**
 * @property-read \ICanBoogie\Application $app
 * @property-read \ICanBoogie\Module\ModuleCollection $modules
 * @property-read \ICanBoogie\HTTP\Request $request
 * @property-read \ICanBoogie\Routing\RouteCollection $routes
 * @property-read \Icybee\Modules\Sites\Site $site
 * @property-read \Icybee\Modules\Users\User $user
 */
class ActionBarTitle extends Element
{
	protected function get_modules()
	{
		return $this->app->modules;
	}

	protected function get_request()
	{
		return $this->app->request;
	}

	protected function get_routes()
	{
		return $this->app->routes;
	}

	protected function get_site()
	{
		return $this->app->site;
	}

	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-title' ]);
	}

	protected function render_inner_html()
	{
		if ($this->user->is_guest || $this->user instanceof Member)
		{
			return '<h1>Icybee</h1>';
		}

		$request = $this->request;

		try
		{
			$route = $request->context->route;
		}
		catch (PropertyNotDefined $e)
		{
			$route = null;
		}

		if (!$route || empty($route->module))
		{
			throw new ElementIsEmpty;
		}

		$label = $this->modules[$route->module]->title;

		$btn_group = null;
		$options = $this->collect_options($route);

		if ($options)
		{
			$menu = (string) new DropdownMenu([

				DropdownMenu::OPTIONS => $options,

				'value' => $request->path

			]);

			$btn_group = <<<EOT
<div class="btn-group">
<div class="dropdown-toggle" data-toggle="dropdown">
<span class="caret"></span>
</div>
$menu
</div>
EOT;
		}

		$label = \Brickrouge\escape($label);
		$url = \Brickrouge\escape($this->app->url_for("admin:$route->module:index"));

		return <<<EOT
<h1><a href="$url">$label</a></h1>
$btn_group
EOT;
	}

	protected function collect_options(Route $route)
	{
		$options = [];
		$user = $this->user;
		$module_id = $route->module;
		$routes = $this->routes;

		#
		# Views on the website (home, list)
		#

		$list_url = $this->site->resolve_view_url("$module_id/list");

		if ($list_url{0} != '#')
		{
			$options[$list_url] = new A($this->t("List page on the website"), $list_url);
		}

		#
		# settings
		#

		if ($user->has_permission(Module::PERMISSION_ADMINISTER, $module_id))
		{
			$config_pattern = "/admin/$module_id/config";

			if ($route->pattern != $config_pattern)
			{
				$r = $routes->find($config_pattern);

				if ($r)
				{
					$url = (string) $this->app->url_for($r);

					if ($options)
					{
						$options[] = false;
					}

					$options[$url] = new A
					(
						$this->t('Configuration', [], [ 'scope' => 'block_title' ]), $url
					);
				}
			}
		}

		#
		# other modules
		#

		$modules = $this->modules;
		$descriptors = $modules->descriptors;
		$category = $descriptors[$module_id][Descriptor::CATEGORY];

		$options_routes = [];

		foreach ($routes as $r_id => $r)
		{
			if (empty($r['index']) || empty($r['module']))
			{
				continue;
			}

			$r_module_id = $r['module'];

			if (!isset($modules[$r_module_id]) || $descriptors[$r_module_id][Descriptor::CATEGORY] != $category)
			{
				continue;
			}

			$permission = isset($r['permission']) ? $r['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $r_module_id))
			{
				continue;
			}

			$url = $this->app->url_for($r);
			$module_flat_id = strtr($r_module_id, '.', '_');

			$options_routes[$url] = new A
			(
				$this->t($module_flat_id, [], [ 'scope' => 'module_title', 'default' => $descriptors[$r_module_id][Descriptor::TITLE] ]), $url
			);
		}

		if ($options_routes)
		{
			if ($options)
			{
				$options[] = false;
			}

			$options = array_merge($options, $options_routes);
		}

		return $options;
	}
}
