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
use ICanBoogie\Routes;

use Brickrouge\Alert;

use Icybee\Modules\Pages\PageController;

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
					'visibility' => 'auto',
					'via' => 'ANY',
					'module' => $module_id
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
	static public function dispatch_query_operation(Request $request)
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
	 * Alters the response location according to the _operation save mode_.
	 *
	 * The following modes are supported:
	 *
	 * - {@link OPERATION_SAVE_MODE_LIST}: Location is the module's index.
	 * - {@link OPERATION_SAVE_MODE_CONTINUE}: Location is the edit URL of the record.
	 * - {@link OPERATION_SAVE_MODE_NEW}: Location is a blank edit form.
	 * - {@link OPERATION_SAVE_MODE_DISPLAY}: Location is the URL of the record.
	 *
	 * The _operation save mode_ is saved in the session per module:
	 *
	 *     $core->session[operation_save_mode][<module_id>]
	 *
	 * @param \ICanBoogie\Operation\BeforeControlEvent $event
	 * @param \ICanBoogie\SaveOperation $target
	 */
	static public function before_save_operation_control(\ICanBoogie\Operation\BeforeControlEvent $event, \ICanBoogie\SaveOperation $target)
	{
		global $core;

		$mode = $event->request[OPERATION_SAVE_MODE];

		if (!$mode)
		{
			return;
		}

		$core->session->operation_save_mode[$target->module->id] = $mode;

		$eh = $core->events->attach(function(\ICanBoogie\Operation\ProcessEvent $event, \ICanBoogie\SaveOperation $operation) use($mode, $target, &$eh) {

			$eh->detach();

			if ($operation != $target || $event->request->uri != $event->response->location)
			{
				return;
			}

			$location = '/admin/' . $target->module->id;

			switch ($mode)
			{
				case OPERATION_SAVE_MODE_CONTINUE:
				{
					$location .= '/' . $event->rc['key'] . '/edit';
				}
				break;

				case OPERATION_SAVE_MODE_NEW:
				{
					$location .= '/new';
				}
				break;

				case OPERATION_SAVE_MODE_DISPLAY:
				{
					try
					{
						$url = $target->record->url;

						if ($url && $url{0} != '#')
						{
							$location = $url;
						}
					}
					catch (\Exception $e)
					{
						return;
					}
				}
				break;
			}

			if ($mode != OPERATION_SAVE_MODE_DISPLAY)
			{
				$location = \ICanBoogie\Routing\contextualize($location);
			}

			$event->response->location = $location;

		});
	}

	/*
	 * Markups
	 */

	/**
	 * Displays the alerts issued during request processing.
	 *
	 * <pre>
	 * <p:alerts>
	 *     <!-- Content: template? -->
	 * </p:alerts>
	 * </pre>
	 *
	 * A marker is placed in the rendered HTML that will later be replaced by the actual alerts.
	 *
	 * The following alerts are displayed: `success`, `info` and `error`. `debug` alert and
	 * displayed if the debug mode is {@link Debug::MODE_DEV}.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return string
	 */
	static public function markup_alerts(array $args, $engine, $template)
	{
		global $core;

		$key = '<!-- alert-markup-placeholder-' . uniqid() . ' -->';

		$core->events->attach
		(
			function(PageController\RenderEvent $event, PageController $target) use($engine, $template, $key)
			{
				$types = array('success', 'info', 'error');

				if (Debug::$mode == Debug::MODE_DEV)
				{
					$types[] = 'debug';
				}

				$alerts = '';

				foreach ($types as $type)
				{
					$alerts .= new Alert(Debug::fetch_messages($type), array(Alert::CONTEXT => $type));
				}

				if ($template)
				{
					$alerts = $engine($template, $alerts);
				}

				$event->html = str_replace($key, $alerts, $event->html);
			}
		);

		return $key;
	}

	/**
	 * The BODY element.
	 *
	 * <pre>
	 * <p:body
	 *     class = string>
	 *     <!-- Content: with-param*, template? -->
	 * </p:body>
	 * </pre>
	 *
	 * The `class` attribute of the element can be specified with the `class` param. It is extended
	 * with the class of the {@link \Icybee\Document} instance.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return string
	 */
	static public function markup_body(array $args, $engine, $template)
	{
		global $core;

		return '<body class="' . trim($args['class'] . ' ' . $core->document->css_class) . '">' . $engine($template) . '</body>';
	}
}