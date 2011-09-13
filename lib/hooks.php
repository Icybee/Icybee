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

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;

class Hooks
{
	public static function synthesize_admin_routes(array $fragments)
	{
		global $core;

		static $specials = array('manage', 'new', 'config', 'edit');

		$rc = array();

		foreach ($fragments as $path => $routes)
		{
			$local_module_id = null;

			if (basename(dirname($path)) == 'modules')
			{
				$local_module_id = basename($path);
			}

			foreach ($routes as $pattern => $route)
			{
				$add_delete_route = false;

				if (isset($route['module']))
				{
					$local_module_id = $route['module'];
				}

				if (in_array($pattern, $specials))
				{
					switch ($pattern)
					{
						case 'manage':
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

						case 'new':
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

						case 'edit':
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

						case 'config':
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
				}

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
					'module' => $module_id,
					'workspace' => $workspace,
					'visibility' => 'visible'
				);

				$rc[$pattern] = $route;

				if ($add_delete_route)
				{
					$rc["/admin/$local_module_id/<\d+>/delete"] = $a = array
					(
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
	public static function dispatch_query_operation(array $params)
	{
		global $core;

		$module = $core->modules[$params['module']];

		$try = get_class($module);
		$class = null;

		while ($try && strpos($try, 'ICanBoogie\Module\\') === 0)
		{
			$class = str_replace('\Module\\', '\Operation\\', $try) . '\QueryOperation';

			if (class_exists($class, true))
			{
				break;
			}

			$class = null;
			$try = get_parent_class($try);
		}

		if (!$class)
		{
			$class = 'Icybee\Operation\Module\QueryOperation';
		}

		return new $class($module, $params);
	}

	/**
	 * This callback is used to delete all the locks set by the user while editing records.
	 *
	 * @param Event $event
	 */
	static public function before_user_disconnect(Event $event)
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

			$names = $registry->select('name')
			->where('name LIKE "admin.locks.%.uid" AND value = ?', $uid)
			->all(\PDO::FETCH_COLUMN);

			if ($names)
			{
				$in = array();

				foreach ($names as $name)
				{
					$in[] = $name;
					$in[] = substr($name, 0, -3) . 'until';
				}

				$registry->where(array('name' => $in))->delete();
			}
		}
		catch (\Exception $e) {  };
	}
}