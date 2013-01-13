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

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Route;
use ICanBoogie\Routes;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Collector;
use Brickrouge\Element;
use Brickrouge\SplitButton;

class Actionbar extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar'));
	}

	protected function render_inner_html()
	{
		global $core;

		$actionbar_new = null;
		$actionbar_navigation = null;
		$actionbar_search = null;
		$actionbar_toolbar = null;

		try
		{
			#
			# This happens when a AuthenticationRequired or PermissionRequired was thrown.
			#

			if (!$core->request)
			{
				throw new PropertyNotDefined();
			}

			$route = $core->request->route;

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
			$actionbar_toolbar = (string) new ActionbarToolbar;
		}
		catch (PropertyNotDefined $e)
		{
			#
			# if route is not defined.
			#

// 			throw new \Brickrouge\ElementIsEmpty;
		}

		$actionbar_title = (string) new ActionbarTitle;

		if (!$actionbar_title && !$actionbar_new && !$actionbar_navigation && !$actionbar_toolbar && !$actionbar_search)
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		return <<<EOT
<div class="pull-left">
	{$actionbar_title}{$actionbar_new}{$actionbar_navigation}
</div>

<div class="pull-right">{$actionbar_search}{$actionbar_toolbar}</div>
EOT;
	}
}

class ActionbarNav extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-nav'));
	}

	protected function render_inner_html()
	{
		global $core;

		$current_route = $core->request->route;
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

	protected function render_link(array $route, Route $current_route)
	{
		global $core;

		$title = $route['title'];

		if ($title{0} == '.') // TODO-20120214: COMPAT
		{
			$title = substr($title, 1);
		}

		$title = I18n\t($title, array(), array('scope' => 'block.title'));
		$pattern = $pathname = $route['pattern'];

		if (Route::is_pattern($pattern))
		{
			$pathname = Route::format($pattern, $core->request->path_params);
		}

		$link = new A($title, \ICanBoogie\Routing\contextualize($pathname), array('class' => 'actionbar-link'));

		if ($pattern == $current_route->pattern)
		{
			$link->add_class('active');
		}

		return $link;
	}

	protected function collect_routes($current_route)
	{
		global $core;

		$collection = array();
		$pattern = $current_route->pattern;

		if (!$current_route->module)
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		$module_id = $current_route->module;
		$user = $core->user;

		$index_pattern = "/admin/$module_id";
		$new_pattern = "/admin/$module_id/new";

		$skip = array
		(
// 			"/admin/$module/config" => true,
			"/admin/$module_id/manage" => true
		);

		foreach (Routes::get() as $route)
		{
			$route_module = isset($route['module']) ? $route['module'] : null;

			if (!$route_module || $route_module != $module_id || empty($route['title']))
			{
				continue;
			}

			$r_pattern = $route['pattern'];

			if ($r_pattern == $index_pattern || $r_pattern == $new_pattern)
			{
				continue;
			}

			/*
			if (($route['visibility'] == 'auto' && $r_pattern != $pattern) || Route::is_pattern($r_pattern) || isset($skip[$r_pattern])))
			{
				continue;
			}
			*/

			if ($r_pattern == $pattern)
			{

			}
			else
			{
				if ((isset($route['visibility']) && $route['visibility'] == 'auto') || Route::is_pattern($r_pattern) || isset($skip[$r_pattern]))
				{
					continue;
				}
			}

			$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$collection[$r_pattern] = $route;
		}

		return $collection;
	}
}

class ActionbarContextNav extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-context-nav'));
	}

	protected function render_inner_html()
	{
		global $core;

		$html = '';
		$current_route = $core->request->route;
		$collection = $this->collect_routes($current_route);

		if (empty($collection))
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		foreach ($collection as $route)
		{
			$html .= $this->render_link($route, $current_route);
		}

		return $html . parent::render_inner_html();
	}

	protected function render_link(array $route, array $current_route)
	{
		$title = $route['title'];

		if ($title{0} == '.') // TODO-20120214: COMPAT
		{
			$title = substr($title, 1);
		}

		$title = I18n\t($title, array(), array('scope' => 'block.title'));
		$pattern = $route['pattern'];

		$link = new A($title, \ICanBoogie\Routing\contextualize($pattern), array('class' => 'actionbar-link'));

		if ($pattern == $current_route['pattern'])
		{
			$link->add_class('active');
		}

		return $link;
	}

	protected function collect_routes($current_route)
	{
		global $core;

		$collection = array();
		$pattern = $current_route->pattern;
		$module = $current_route->module;
		$user = $core->user;

		foreach (Route::routes() as $route)
		{
			$route_pattern = $route['pattern'];
			$route_module = isset($route['module']) ? $route['module'] : null;

			if (!$route_module || $route_module != $module || empty($route['title']))
			{
				continue;
			}

			$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module))
			{
				continue;
			}

			if (Route::is_pattern($route_pattern) && $route_pattern != $pattern)
			{
				continue;
			}

			$collection[$route_pattern] = $route;
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

	public function __toString()
	{
		global $core;

		$route = $core->request->route;
		$module_id = $route->module;
		$match = Routes::get()->find("/admin/$module_id/new");

		$this->render_as_button = !$match;

		if ($route->pattern != '/admin/dashboard' && !$match)
		{
			return '';
		}

		return parent::__toString();
	}

	protected function collect_routes()
	{
		global $core;

		$collection = array();
		$translations = array();

		$routes = Routes::get();
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
					'default' => \ICanBoogie\singularize(I18n\t("module_title.$flat_id", array(), array('default' => $descriptors[$module_id][Module::T_TITLE])))
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