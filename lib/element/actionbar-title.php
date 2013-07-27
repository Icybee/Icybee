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
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Route;
use ICanBoogie\Routes;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;
use Brickrouge\SplitButton;

class ActionbarTitle extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-title'));
	}

	protected function render_inner_html()
	{
		global $core;

		if ($core->user->is_guest || $core->user instanceof Member)
		{
			return '<h1>Icybee</h1>';
		}

		$request = $core->request;

		try
		{
			$route = $request->route;
		}
		catch (PropertyNotDefined $e)
		{
			$route = null;
		}

		if (!$route || !$route->module)
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		$label = $core->modules[$route->module]->title;

		$btn_group = null;
		$options = $this->collect_options($route);

		if ($options)
		{
			$menu = (string) new DropdownMenu
			(
				array
				(
					DropdownMenu::OPTIONS => $options,

					'value' => $request->path
				)
			);

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
		$url = \Brickrouge\escape(\ICanBoogie\Routing\contextualize('/admin/' . $route->module));

		return <<<EOT
<h1><a href="$url">$label</a></h1>
$btn_group
EOT;
	}

	protected function collect_options(Route $route)
	{
		global $core;

		$options = array();

		$user = $core->user;
		$module_id = $route->module;
		$routes = Routes::get();

		#
		# Views on the website (home, list)
		#

		$list_url = $core->site->resolve_view_url("$module_id/list");

		if ($list_url{0} != '#')
		{
			$options[$list_url] = new A(I18n\t("List page on the website"), $list_url);
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
					$url = \ICanBoogie\Routing\contextualize((string) $r->pattern);

					if ($options)
					{
						$options[] = false;
					}

					$options[$url] = new A
					(
						I18n\t('Configuration', array(), array('scope' => 'block_title')), $url
					);
				}
			}
		}

		#
		# other modules
		#

		$modules = $core->modules;
		$descriptors = $modules->descriptors;
		$category = $descriptors[$module_id][Module::T_CATEGORY];

		$options_routes = array();

		foreach ($routes as $r_id => $r)
		{
			if (empty($r['index']) || empty($r['module']))
			{
				continue;
			}

			$r_module_id = $r['module'];

			if (!isset($modules[$r_module_id]) || $descriptors[$r_module_id][Module::T_CATEGORY] != $category)
			{
				continue;
			}

			$permission = isset($r['permission']) ? $r['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $r_module_id))
			{
				continue;
			}

			$url = \ICanBoogie\Routing\contextualize($r['pattern']);
			$module_flat_id = strtr($r_module_id, '.', '_');

			$options_routes[$url] = new A
			(
				I18n\t($module_flat_id, array(), array('scope' => 'module_title', 'default' => $descriptors[$r_module_id][Module::T_TITLE])), $url
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