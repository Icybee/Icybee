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
use ICanBoogie\HTTP\NotFound;
use ICanBoogie\HTTP\RequestDispatcher;
use ICanBoogie\HTTP\Exception as HTTPError;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\HTTP\WeightedDispatcher;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Operation;
use ICanBoogie\Routing;

use Brickrouge\Alert;
use Brickrouge\Document;

use ICanBoogie\View\View;
use Icybee\Binding\CoreBindings;
use Icybee\Modules\Pages\PageRenderer;
use Icybee\Routing\AdminController;

class Hooks
{
	/*
	 * Events
	 */

	static public function on_http_dispatcher_alter(RequestDispatcher\AlterEvent $event, RequestDispatcher $dispatcher)
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
			$app = self::app();
			$path = \ICanBoogie\normalize_url_path(Routing\decontextualize($request->path));

			if (strpos($path, '/admin/') !== 0)
			{
				return null;
			}

			$category = substr($path, 7, -1);

			if ($category)
			{
				$user = $app->user;
				$routes = $app->routes;

				foreach ($app->modules->descriptors as $module_id => $descriptor)
				{
					if (!isset($app->modules[$module_id])
					|| !$user->has_permission(Module::PERMISSION_ACCESS, $module_id)
					|| $descriptor[Descriptor::CATEGORY] != $category)
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

					return new RedirectResponse(self::app()->url_for($route), 302, [

						'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__

					]);
				}
			}
		}, 'before:pages');
	}

	/**
	 * This is the dispatcher for the QueryOperation operation.
	 *
	 * @param Request $request
	 *
	 * @return Operation
	 */
	static public function dispatch_query_operation(Request $request)
	{
		$app = self::app();
		$class = 'Icybee\Operation\Module\QueryOperation';
		$try_module = $module = $app->modules[$request['module']];

		while ($try_module)
		{
			$try = Operation::format_class_name($try_module->descriptor[Descriptor::NS], 'QueryOperation');

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
	 * Delete locks set by the user while editing records.
	 *
	 * @param Operation\BeforeProcessEvent $event
	 */
	static public function before_user_logout(Operation\BeforeProcessEvent $event)
	{
		$app = self::app();
		$uid = $app->user_id;

		if (!$uid)
		{
			return;
		}

		try
		{
			$app->registry
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
	 *     $app->session[operation_save_mode][<module_id>]
	 *
	 * @param Operation\BeforeControlEvent $event
	 * @param \ICanBoogie\Module\Operation\SaveOperation $target
	 */
	static public function before_save_operation_control(Operation\BeforeControlEvent $event, \ICanBoogie\Module\Operation\SaveOperation $target)
	{
		$app = self::app();
		$mode = $event->request[OPERATION_SAVE_MODE];

		if (!$mode)
		{
			return;
		}

		$app->session->operation_save_mode[$target->module->id] = $mode;

		$app->events->once(function(Operation\ProcessEvent $event, \ICanBoogie\Module\Operation\SaveOperation $operation) use($mode, $target) {

			if ($operation != $target || $event->request->uri != $event->response->location)
			{
				return;
			}

			$location = '/admin/' . $target->module->id;

			switch ($mode)
			{
				case OPERATION_SAVE_MODE_CONTINUE:

					$location .= '/' . $event->rc['key'] . '/edit';

					break;

				case OPERATION_SAVE_MODE_NEW:

					$location .= '/create';

					break;

				case OPERATION_SAVE_MODE_DISPLAY:

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

					break;
			}

			if ($mode != OPERATION_SAVE_MODE_DISPLAY)
			{
				$location = Routing\contextualize($location);
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
		self::app()->events->attach(function(\BlueTihi\Context\LoadedNodesEvent $event, \BlueTihi\Context $target) {

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

	/**
	 * If the view renders a module's route, the "template" directory of that module is added
	 * to the list of templates locations.
	 *
	 * @param View\AlterEvent $event
	 * @param View $target
	 */
	static public function on_view_alter(View\AlterEvent $event, View $target)
	{
		if (!$target->controller instanceof AdminController)
		{
			return;
		}

		$target->template_resolver->add_path(DIR . 'templates');
	}

	/**
	 * Rescues NotFound exceptions, when the index of a module category is requested.
	 *
	 * @param \ICanBoogie\Exception\RescueEvent $event
	 * @param NotFound $exception
	 */
	static public function on_exception_rescue(\ICanBoogie\Exception\RescueEvent $event, NotFound $exception)
	{
		$request = $event->request;

		if (!preg_match('#\/admin\/([^\/\?]+)#', $request->uri, $matches))
		{
			return;
		}

		$category = $matches[1];
		$app = self::app();
		$routes = $app->routes;

		foreach ($app->modules->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if (empty($descriptor[Descriptor::CATEGORY])
			|| $descriptor[Descriptor::CATEGORY] != $category)
			{
				continue;
			}

			$route_id = "admin:$module_id:index";

			if (!isset($routes[$route_id]))
			{
				continue;
			}

			$event->response = new RedirectResponse(self::app()->url_for($route_id));
		}
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
	static public function markup_alerts(array $args, \Patron\Engine $engine, $template)
	{
		$key = '<!-- alert-markup-placeholder-' . uniqid() . ' -->';

		self::app()->events->attach(function(PageRenderer\RenderEvent $event, PageRenderer $target) use($engine, $template, $key) {

			$types = [ 'success', 'info', 'error' ];

			if (Debug::$mode == Debug::MODE_DEV)
			{
				$types[] = 'debug';
			}

			$alerts = '';

			foreach ($types as $type)
			{
				$alerts .= new Alert(Debug::fetch_messages($type), [ Alert::CONTEXT => $type ]);
			}

			if ($template)
			{
				$alerts = $engine($template, $alerts);
			}

			$event->html = str_replace($key, $alerts, $event->html);

		});

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
		return '<body class="' . trim($args['class'] . ' ' . \ICanBoogie\app()->document->css_class) . '">' . $engine($template) . '</body>';
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
		$app = \ICanBoogie\app();
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

		$formated_exception = Debug::format_alert($exception);
		$reported = false;

		if (!($exception instanceof HTTPError))
		{
			Debug::report($formated_exception);

			$reported = true;
		}

		if (!headers_sent() && PHP_SAPI != 'cli')
		{
			$site = isset($app->site) ? $app->site : null;

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

			ob_start();

			require __DIR__ . '/exception.tpl.php';

			$formated_exception = ob_get_clean();
		}

		if (PHP_SAPI == 'cli')
		{
			$formated_exception = strip_tags($formated_exception);
		}

		echo $formated_exception;
	}

	/*
	 * Prototype methods
	 */

	/**
	 * Returns the current language.
	 *
	 * **Note:** The language is returned from the stringified `$app->locale` property.
	 *
	 * @param \ICanBoogie\Core|\ICanBoogie\Binding\CLDR\CoreBindings $app
	 *
	 * @return string
	 */
	static public function get_language(\ICanBoogie\Core $app)
	{
		return (string) $app->locale;
	}

	/**
	 * Sets the current language.
	 *
	 * **Note:** The language is set to the `$app->locale` property.
	 *
	 * @param \ICanBoogie\Core|\ICanBoogie\Binding\CLDR\CoreBindings $app
	 * @param string $language
	 */
	static public function set_language(\ICanBoogie\Core $app, $language)
	{
		$app->locale = $language;
	}

	/*
	 * Support
	 */

	/**
	 * @return \ICanBoogie\Core|CoreBindings
	 */
	static private function app()
	{
		return \ICanBoogie\app();
	}
}
