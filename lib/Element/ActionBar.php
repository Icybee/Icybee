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

use Icybee\Modules\Members\Member;
use Icybee\Modules\Users\User;
use Icybee\Routing\RouteMaker;

/**
 * Class ActionBar
 *
 * @package Icybee\Element
 *
 * @property-read \ICanBoogie\Core|\Icybee\Binding\CoreBindings $app
 * @property-read ModuleCollection $modules
 * @property-read Request $request
 * @property-read RouteCollection $routes
 * @property-read User $user
 */
class ActionBar extends Element
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

	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [

			Element::IS => 'action-bar',

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
				throw new PropertyNotDefined("There is no request");
			}

			$route = $this->request->context->route;

			if (!$this->user->is_guest && !($this->user instanceof Member))
			{
				$module_id = $route->module;

				$actionbar_new = (string) new ActionbarNew('New', [

					ActionbarNew::ID => RouteMaker::ADMIN_PREFIX . "$module_id:" . RouteMaker::ACTION_NEW,
					ActionbarNew::ROUTE => $route

				]);
			}

			$actionbar_navigation = (string) new ActionBarNav;
			$actionbar_search = (string) new ActionBarSearch;
			$actionbar_controls = (string) new ActionBarToolbar;
		}
		catch (PropertyNotDefined $e)
		{
			#
			# if route is not defined.
			#

// 			throw new \Brickrouge\ElementIsEmpty;
		}

		$actionbar_title = (string) new ActionBarTitle;

		if (!$actionbar_title && !$actionbar_new && !$actionbar_navigation && !$actionbar_controls && !$actionbar_search)
		{
			throw new ElementIsEmpty;
		}

		$actionbar_contexts = (string) new ActionBarContexts;

		return <<<EOT
<div class="actionbar-primary">
	<div class="actionbar-brand pull-xs-left">
		{$actionbar_title}{$actionbar_new}{$actionbar_navigation}
	</div>

	<div class="pull-xs-right">
		<div class="actionbar-controls">{$actionbar_controls}</div>
		<div class="actionbar-search">{$actionbar_search}</div>
	</div>
</div>

$actionbar_contexts
EOT;
	}
}

