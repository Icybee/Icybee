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
use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\HTTP\WeightedDispatcher;
use ICanBoogie\Operation;

use Brickrouge\Alert;
use Brickrouge\Document;

use Icybee\Modules\Pages\PageRenderer;

class Hooks
{
	/*
	 * Events
	 */

	static public function on_http_dispatcher_alter(Dispatcher\AlterEvent $event, Dispatcher $dispatcher)
	{
		/**
		 * Router for admin routes.
		 *
		 * This event hook handles all "/admin/" routes. It may redirect the user to the proper "admin"
		 * location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the Icybee admin
		 * interface is presented, granted the user has an access permission, otherwise the
		 * user is asked to authenticate.
		 */
		$dispatcher['admin:categories'] = new WeightedDispatcher(function(Request $request)
		{
			global $core;

			$path = \ICanBoogie\normalize_url_path(\ICanBoogie\Routing\decontextualize($request->path));

			if (strpos($path, '/admin/') !== 0)
			{
				return;
			}

			$category = substr($path, 7, -1);

			if ($category)
			{
				$user = $core->user;
				$routes = $core->routes;

				foreach ($core->modules->descriptors as $module_id => $descriptor)
				{
					if (!isset($core->modules[$module_id]) || !$user->has_permission(Module::PERMISSION_ACCESS, $module_id)
					|| $descriptor[Module::T_CATEGORY] != $category)
					{
						continue;
					}

					$route_id = "admin:$module_id";

					if (empty($routes[$route_id]))
					{
						$route_id = "admin:$module_id/manage"; //TODO-20120829: COMPAT, 'manage' should disappear.

						if (empty($routes[$route_id]))
						{
							continue;
						}
					}

					$route = $routes[$route_id];

					return new RedirectResponse
					(
						\ICanBoogie\Routing\contextualize($route->pattern), 302, array
						(
							'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
						)
					);
				}
			}
		}, 'before:pages');
	}

	static public function before_routing_collect_routes(\ICanBoogie\Routing\BeforeCollectRoutesEvent $event)
	{
		static $magic = [

			'!admin:manage' => true,
			'!admin:new' => true,
			'!admin:config' => true,
			'!admin:edit' => true

		];

		$fragments = [];

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

	static private $page_controller_loaded_nodes = [];

	/**
	 * Attaches a hook to the `BlueTihi\Context::loaded_nodes` event to provide data for the
	 * admin menu. The data is consumed by {@link on_page_renderer_render}.
	 */
	static public function before_page_renderer_render()
	{
		global $core;

		$core->events->attach(function(\BlueTihi\Context\LoadedNodesEvent $event, \BlueTihi\Context $target) {

			$nodes = &self::$page_controller_loaded_nodes;

			foreach ($event->nodes as $node)
			{
				if (!$node instanceof \Icybee\Modules\Nodes\Node)
				{
					\ICanBoogie\log('Not a node object: {0}', [ $node ]);

					continue;
				}

				$nodes[$node->nid] = $node;
			}

		});
	}

	/**
	 * Adds the AdminMenu to pages rendered by the page controller.
	 *
	 * @param \Icybee\Modules\Pages\PageRenderer\RenderEvent $event
	 * @param \Icybee\Modules\Pages\PageRenderer $target
	 */
	static public function on_page_renderer_render(\Icybee\Modules\Pages\PageRenderer\RenderEvent $event, \Icybee\Modules\Pages\PageRenderer $target)
	{
		$admin_menu = (string) new Element\AdminMenu([

			Element\AdminMenu::NODES => self::$page_controller_loaded_nodes

		]);

		if ($admin_menu)
		{
			$event->html = str_replace('</body>', $admin_menu . '</body>', $event->html);
		}
	}

	/*
	 * Prototypes
	 */

	static public function get_cldr()
	{
		static $cldr;

		if (!$cldr)
		{
			$provider = new \ICanBoogie\CLDR\Provider
			(
				new \ICanBoogie\CLDR\RunTimeCache(new \ICanBoogie\CLDR\FileCache(\ICanBoogie\REPOSITORY . 'cldr' . DIRECTORY_SEPARATOR)),
				new \ICanBoogie\CLDR\Retriever
			);

			$cldr = new \ICanBoogie\CLDR\Repository($provider);
		}

		return $cldr;
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
			function(PageRenderer\RenderEvent $event, PageRenderer $target) use($engine, $template, $key)
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

	/*
	 *
	 */

	/**
	 * Exception handler.
	 *
	 * @param \Exception $exception
	 */
	static public function exception_handler(\Exception $exception)
	{
		global $core;

		$code = $exception->getCode() ?: 500;
		$message = $exception->getMessage();
		$class = get_class($exception); // The $class variable is required by the template

		if (!headers_sent())
		{
			$normalized_message = strip_tags($message);
			$normalized_message = str_replace([ "\r\n", "\n" ], ' ', $normalized_message);
			$normalized_message = mb_convert_encoding($normalized_message, \ICanBoogie\CHARSET, 'ASCII');

			if (strlen($normalized_message) > 32)
			{
				$normalized_message = mb_substr($normalized_message, 0, 29) . '…';
			}

			header('HTTP/1.0 ' . $code . ' ' . $class . ': ' . $normalized_message);
			header('X-ICanBoogie-Exception: ' . \ICanBoogie\strip_root($exception->getFile()) . '@' . $exception->getLine());
		}

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
		{
			$rc = json_encode([ 'rc' => null, 'errors' => [ '_base' => $message ] ]);

			header('Content-Type: application/json');
			header('Content-Length: ' . strlen($rc));

			exit($rc);
		}

		$formated_exception = Debug::format_alert($exception);
		$reported = false;

		if (!($exception instanceof HTTPError))
		{
			Debug::report($formated_exception);

			$reported = true;
		}

		if (!headers_sent())
		{
			$site = isset($core->site) ? $core->site : null;

			if (class_exists('Brickrouge\Document'))
			{
				$css = [

					Document::resolve_url(\Brickrouge\ASSETS . 'brickrouge.css'),
					Document::resolve_url(ASSETS . 'admin.css'),
					Document::resolve_url(ASSETS . 'admin-more.css')

				];
			}
			else
			{
				$css = [];
			}

			$formated_exception = require(__DIR__ . '/exception.tpl.php');
		}

		exit($formated_exception);
	}
}