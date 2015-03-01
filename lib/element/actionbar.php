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
use ICanBoogie\Module\Descriptor;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Routing\Pattern;
use ICanBoogie\Routing\Route;

use Brickrouge\A;
use Brickrouge\Collector;
use Brickrouge\Element;
use Brickrouge\SplitButton;

class Actionbar extends Element
{
	public function __construct(array $attributes=[])
	{
		parent::__construct('div', $attributes + [

			Element::IS => 'ActionBar',

			'class' => 'actionbar',
			'data-context' => ''

		]);
	}

	protected function render_inner_html()
	{
		global $core;

		$actionbar_new = null;
		$actionbar_navigation = null;
		$actionbar_search = null;
		$actionbar_controls = null;

		try
		{
			#
			# This happens when a AuthenticationRequired or PermissionRequired was thrown.
			#

			if (!$core->request)
			{
				throw new PropertyNotDefined();
			}

			$route = $core->request->context->route;

			if (!$core->user->is_guest && !($core->user instanceof \Icybee\Modules\Members\Member))
			{
				$module_id = $route->module;

				$actionbar_new = (string) new ActionbarNew
				(
					'New', array
					(
						ActionbarNew::PATTERN => "/admin/$module_id/new",
						ActionbarNew::ROUTE => $route
					)
				);
			}

			$actionbar_navigation = (string) new ActionbarNav;
			$actionbar_search = (string) new ActionbarSearch;
			$actionbar_controls = (string) new ActionbarToolbar;
		}
		catch (PropertyNotDefined $e)
		{
			#
			# if route is not defined.
			#

// 			throw new \Brickrouge\ElementIsEmpty;
		}

		$actionbar_title = (string) new ActionbarTitle;

		if (!$actionbar_title && !$actionbar_new && !$actionbar_navigation && !$actionbar_controls && !$actionbar_search)
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		$actionbar_contexts = (string) new ActionbarContexts;

		return <<<EOT
<div class="actionbar-primary">
	<div class="actionbar-brand pull-left">
		{$actionbar_title}{$actionbar_new}{$actionbar_navigation}
	</div>

	<div class="pull-right">
		<div class="actionbar-controls">{$actionbar_controls}</div>
		<div class="actionbar-search">{$actionbar_search}</div>
	</div>
</div>

$actionbar_contexts
EOT;
	}
}

class ActionbarNav extends Element
{
	public function __construct(array $attributes=[])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-nav' ]);
	}

	protected function render_inner_html()
	{
		global $core;

		$current_route = $core->request->context->route;
		$collection = $this->collect_routes($current_route);

		if (empty($collection))
		{
			throw new \Brickrouge\ElementIsEmpty;
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
		global $core;

		$title = $route->title;

		if ($title{0} == '.') // TODO-20120214: COMPAT
		{
			$title = substr($title, 1);
		}

		$title = I18n\t($title, [], [ 'scope' => 'block.title' ]);

		$formatted_route = $route->format($core->request->path_params);

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
			throw new \Brickrouge\ElementIsEmpty;
		}

		$app = $this->app;
		$collection = [];
		$pattern = $current_route->pattern;
		$module_id = $current_route->module;
		$user = $app->user;
		$index_pattern = "/admin/$module_id";
		$new_pattern = "/admin/$module_id/new";

		$skip = array
		(
// 			"/admin/$module/config" => true,
			"/admin/$module_id/manage" => true
		);

		/* @var $routes \ICanBoogie\Routing\Routes */

		$routes = $app->routes;

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
				if ((isset($route_definition['visibility']) && $route_definition['visibility'] == 'auto') || Pattern::is_pattern($r_pattern) || isset($skip[$r_pattern]))
				{
					continue;
				}
			}

			$permission = isset($route_definition['permission']) ? $route_definition['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$collection[$r_pattern] = $routes[$route_id];
		}

		return $collection;
	}
}

class ActionbarNew extends SplitButton
{
	const PATTERN = '#abn-pattern';
	const ROUTE = '#abn-route';

	public function __construct($label, array $attributes=array())
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

	private $render_as_button=false;

	protected function render_splitbutton_label($label, $class)
	{
		if ($this->render_as_button)
		{
			return '';
		}

		return new A($label, \ICanBoogie\Routing\contextualize($this[self::PATTERN]), array('class' => 'btn ' . $class));
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
		global $core;

		$route = $core->request->context->route;
		$module_id = $route->module;
		$match = $core->routes->find("/admin/$module_id/new");

		$this->render_as_button = !$match;

		if ($route->pattern != '/admin/dashboard' && !$match)
		{
			return '';
		}

		return parent::render();
	}

	protected function collect_routes()
	{
		global $core;

		$collection = array();
		$translations = array();

		$routes = $core->routes;
		$descriptors = $core->modules->descriptors;
		$user = $core->user;

		foreach ($routes as $route)
		{
			$pattern = $route['pattern'];

			if (!preg_match('#/new$#', $pattern))
			{
				continue;
			}

			$module_id = $route['module'];

			if (!isset($core->modules[$module_id]) || !$user->has_permission(Module::PERMISSION_CREATE, $module_id))
			{
				continue;
			}

			$collection[$pattern] = $module_id;

			$flat_id = strtr($module_id, '.', '_');

			$translations[$module_id] = I18n\t
			(
				$flat_id . '.name', array(':count' => 1), array
				(
					'default' => \ICanBoogie\singularize(I18n\t("module_title.$flat_id", array(), array('default' => $descriptors[$module_id][Descriptor::TITLE])))
				)
			);
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
