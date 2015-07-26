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

use Brickrouge\ElementIsEmpty;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Module\ModuleCollection;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Routing\Route;
use ICanBoogie\Routing\RouteCollection;

use Brickrouge\Element;

use Icybee\Modules\Users\User;

/**
 * Class Actionbar
 *
 * @package Icybee\Element
 *
 * @property-read \ICanBoogie\Core $app
 * @property-read ModuleCollection $modules
 * @property-read Request $request
 * @property-read RouteCollection $routes
 * @property-read User $user
 */
class Actionbar extends Element
{
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

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [

			Element::IS => 'ActionBar',

			'class' => 'actionbar',
			'data-context' => ''

		]);
	}

	protected function render_inner_html()
	{
		$actionbar_new = null;
		$actionbar_navigation = null;
		$actionbar_search = null;
		$actionbar_controls = null;

		try
		{
			#
			# This happens when a AuthenticationRequired or PermissionRequired was thrown.
			#

			if (!$this->request)
			{
				throw new PropertyNotDefined("There is not request");
			}

			$route = $this->request->context->route;

			if (!$this->user->is_guest && !($this->user instanceof \Icybee\Modules\Members\Member))
			{
				$module_id = $route->module;

				$actionbar_new = (string) new ActionbarNew('New', [

					ActionbarNew::PATTERN => "/admin/$module_id/new",
					ActionbarNew::ROUTE => $route

				]);
			}

			$actionbar_navigation = (string) new ActionBarNav;
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
			throw new ElementIsEmpty;
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

