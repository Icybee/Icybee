<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Events;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Modules\Pages\PageController;
use ICanBoogie\Operation;
use ICanBoogie\Routes;

use Brickrouge\Alert;

class Hooks
{
	static public function before_routes_collect(Routes\BeforeCollectEvent $event, Routes $target)
	{
		static $magic = array
		(
			'!admin:manage' => true,
			'!admin:new' => true,
			'!admin:config' => true,
			'!admin:edit' => true
		);

		$fragments = array();

		foreach ($event->fragments as $root => $fragment)
		{
			$add_delete_route = false;

			foreach ($fragment as $id => $route)
			{
				if (empty($magic[$id]))
				{
					if (isset($route['block']) && empty($route['controller']))
					{
						$route['controller'] = 'Icybee\BlockController';
					}

					$fragments[$root][$id] = $route;

					continue;
				}

				$module_id = $route['module'];

				switch ($id)
				{
					case '!admin:manage':
					{
						$id = "admin:$module_id/manage"; // TODO-20120828: renamed this as "admin:{module_id}"

						$route += array
						(
							'pattern' => "/admin/$module_id",
							'controller' => 'Icybee\BlockController',
							'title' => '.manage',
							'block' => 'manage',
							'index' => true
						);
					}
					break;

					case '!admin:new':
					{
						$id = "admin:$module_id/new";

						$route += array
						(
							'pattern' => "/admin/$module_id/new",
							'controller' => 'Icybee\BlockController',
							'title' => '.new',
							'block' => 'edit',
							'visibility' => 'visible'
						);
					}
					break;

					case '!admin:edit':
					{
						$id = "admin:$module_id/edit";

						$route += array
						(
							'pattern' => "/admin/$module_id/<\d+>/edit",
							'controller' => 'Icybee\EditController',
							'title' => '.edit',
							'block' => 'edit',
							'visibility' => 'auto'
						);

						$add_delete_route = true;
					}
					break;

					case '!admin:config':
					{
						$id = "admin:$module_id/config";

						$route += array
						(
							'pattern' => "/admin/$module_id/config",
							'controller' => 'Icybee\BlockController',
							'title' => '.config',
							'block' => 'config',
							'permission' => Module::PERMISSION_ADMINISTER,
							'visibility' => 'visible'
						);
					}
					break;
				}

				$fragments[$root][$id] = $route;
			}

			if ($add_delete_route)
			{
				$fragments[$root]["admin:$module_id/delete"] = array
				(
					'pattern' => "/admin/$module_id/<\d+>/delete",
					'controller' => 'Icybee\BlockController',
					'title' => '.delete',
					'block' => 'delete',
					'visibility' => 'auto'
				);
			}
		}

		#
		# default redirection from categories to a module.
		#

		$event->fragments = $fragments;
	}

	/**
	 * This is the dispatcher for the QueryOperation operation.
	 *
	 * @param array $params
	 *
	 * @return Operation
	 */
	public static function dispatch_query_operation(Request $request)
	{
		global $core;

		$class = 'Icybee\Operation\Module\QueryOperation';
		$try_module = $module = $core->modules[$request['module']];

		while ($try_module)
		{
			$try = Operation::format_class_name($try_module->descriptor[Module::T_NAMESPACE], 'QueryOperation');

			if (class_exists($try, true))
			{
				$class = $try;

				break;
			}

			$try_module = $try_module->parent;
		}

		return new $class($module, $request);
	}

	/**
	 * This callback is used to delete all the locks set by the user while editing records.
	 *
	 * @param Event $event
	 */
	static public function before_user_logout(Event $event)
	{
		global $core;

		$uid = $core->user_id;

		if (!$uid)
		{
			return;
		}

		try
		{
			$registry = $core->registry;

			$names = $registry
			->where('name LIKE "activerecord_locks.%" AND value LIKE ?', '{"uid":"' . $uid . '"%')
			->delete();
		}
		catch (\Exception $e) { };
	}

	/**
	 * Displays the alerts issued during request processing.
	 *
	 * A marker is placed in the rendered HTML that will later be replaced by the actual alerts.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return string
	 */
	public static function markup_alerts(array $args, $engine, $template)
	{
		$key = '<!-- alert-markup-placeholder-' . md5(uniqid()) . ' -->';

		Events::attach
		(
			'ICanBoogie\Modules\Pages\PageController::render', function(PageController\RenderEvent $event, PageController $target) use($engine, $template, $key)
			{
				$types = array('success', 'info', 'error');

				if (Debug::$mode == Debug::MODE_DEV)
				{
					$types[] = 'debug';
				}

				$alert = '';

				foreach ($types as $type)
				{
					$alert .= new Alert(Debug::fetch_messages($type), array(Alert::CONTEXT => $type));
				}

				if ($template)
				{
					$alert = $engine($template, $alert);
				}

				$event->html = str_replace($key, $alert, $event->html);
			}
		);

		return $key;
	}
}