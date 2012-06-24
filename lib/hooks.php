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
use ICanBoogie\Operation;

use Brickrouge\Alert;

class Hooks
{
	public static function synthesize_admin_routes(array $fragments)
	{
		global $core;

		static $specials = array(':admin/manage', ':admin/new', ':admin/config', ':admin/edit');

		$rc = array();

		foreach ($fragments as $path => $routes)
		{
			$local_module_id = null;

			if (basename(dirname($path)) == 'modules')
			{
				$local_module_id = basename($path);
			}

			foreach ($routes as $route_id => $route)
			{
				$add_delete_route = false;

				if (isset($route['module']))
				{
					$local_module_id = $route['module'];
				}

				$pattern = isset($route['pattern']) ? $route['pattern'] : null;

				if (in_array($route_id, $specials))
				{
					switch ($route_id)
					{
						case ':admin/manage':
						{
							$pattern = "/admin/$local_module_id";

							$route += array
							(
								'title' => '.manage',
								'block' => 'manage',
								'index' => true,
								'module' => $local_module_id,
								'visibility' => 'visible'
							);
						}
						break;

						case ':admin/new':
						{
							$pattern = "/admin/$local_module_id/new";

							$route += array
							(
								'title' => '.new',
								'block' => 'edit',
								'module' => $local_module_id,
								'visibility' => 'visible'
							);
						}
						break;

						case ':admin/edit':
						{
							$pattern = "/admin/$local_module_id/<\d+>/edit";

							$route += array
							(
								'title' => '.edit',
								'block' => 'edit',
								'module' => $local_module_id,
								'visibility' => 'auto'
							);

							$add_delete_route = true;
						}
						break;

						case ':admin/config':
						{
							$pattern = "/admin/$local_module_id/config";

							$route += array
							(
								'title' => '.config',
								'block' => 'config',
								'module' => $local_module_id,
								'permission' => Module::PERMISSION_ADMINISTER,
								'visibility' => 'visible'
							);
						}
						break;
					}

					$route_id = $local_module_id . $route_id;
				}

				/*
				if (empty($route['pattern']))
				{
					throw new \LogicException(t
					(
						"Route %route_id has no pattern in %path. !route", array
						(
							'%route_id' => $route_id,
							'%path' => $path,
							'!route' => $route
						)
					));
				}

				$pattern = $route['pattern'];
				*/

				if (substr($pattern, 0, 7) != '/admin/')
				{
					continue;
				}

				if (isset($route['block']) && empty($route['module']))
				{
					$route['module'] = $local_module_id;
				}

				$module_id = isset($route['module']) ? $route['module'] : $local_module_id;

				if ($module_id && !isset($core->modules[$module_id]))
				{
					continue;
				}

				#
				# workspace
				#

				$workspace = null;

				if ($module_id && isset($core->modules->descriptors[$module_id]) )
				{
					$descriptor = $core->modules->descriptors[$module_id];

					if (empty($route['workspace']) && isset($descriptor[Module::T_CATEGORY]))
					{
						$workspace = $descriptor[Module::T_CATEGORY];
					}
					else
					{
						list($workspace) = explode('.', $module_id);
					}
				}

				$route += array
				(
					'pattern' => $pattern,
					'module' => $module_id,
					'workspace' => $workspace,
					'visibility' => 'visible'
				);

				$rc[$route_id] = $route;

				if ($add_delete_route)
				{
					$rc["/admin/$local_module_id/delete"] = $a = array
					(
						'pattern' => "/admin/$local_module_id/<\d+>/delete",
						'title' => '.delete',
						'block' => 'delete'
					)

					+ $route;
				}
			}
		}

		return $rc;
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

	public static function on_alter_cache_collection(Event $event, \ICanBoogie\Modules\System\Cache\Collection $collection)
	{
		$event->collection['icybee.views'] = new \Icybee\Views\CacheManager;
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
			'Icybee\Pagemaker::render', function(\Icybee\Pagemaker\RenderEvent $event) use($engine, $template, $key)
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