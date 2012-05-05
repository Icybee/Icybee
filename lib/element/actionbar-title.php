<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Admin\Element;

use ICanBoogie\Module;
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
		$route = $request->route;

		if (!$route || !$route->module)
		{
			throw new \Brickrouge\EmptyElementException;
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
		$url = \Brickrouge\escape(Route::contextualize('/admin/' . $route->module));

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
		$modules = $core->modules;
		$descriptors = $modules->descriptors;
		$category = $descriptors[$module_id][Module::T_CATEGORY];

		$routes = Routes::get();

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

			$url = Route::contextualize($r['pattern']);

			$options[$url] = new A
			(
				t($descriptors[$r_module_id][Module::T_TITLE], array(), array('scope' => 'module_title')), $url
			);
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
					$url = Route::contextualize($r->pattern);

					$options[] = false;
					$options[$url] = new A
					(
						t('Configuration', array(), array('scope' => 'block_title')), $url
					);
				}
			}
		}

		#
		# Views on the website (home, list)
		#

		$list_url = $core->site->resolve_view_url("$module_id/list");

		if ($list_url{0} != '#')
		{
			$options[] = false;
			$options[$list_url] = new A(t("List page on the website"), $list_url);
		}

		return $options;
	}
}