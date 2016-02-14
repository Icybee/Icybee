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
use ICanBoogie\HTTP\Status;
use ICanBoogie\Module\Descriptor;
use ICanBoogie\Operation;
use ICanBoogie\Render\BasicTemplateResolver;
use ICanBoogie\Render\TemplateResolver;
use ICanBoogie\Render\TemplateResolverDecorator;
use ICanBoogie\Routing;
use ICanBoogie\View\View;

use Brickrouge\Alert;

use Icybee\Binding\CoreBindings;
use Icybee\Modules\Pages\PageRenderer;
use Icybee\Operation\Module\QueryOperation;
use Icybee\Routing\AdminController;
use Icybee\Routing\RouteMaker;

class Hooks
{
	/*
	 * Events
	 */

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
		$module = $app->modules[$request['module']];
		$class = $app->modules->resolve_classname('Operation\QueryOperationOperation', $module)
			?: QueryOperation::class;

		$operation = $class::from([ 'module' => $module ]);

		return $operation($request);
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
	 *     $app->session['operation_save_mode'][<module_id>]
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

		$app->session['operation_save_mode'][$target->module->id] = $mode;

		$app->events->once(function(Operation\ProcessEvent $event, \ICanBoogie\Module\Operation\SaveOperation $operation) use($mode, $target) {

			if ($operation != $target || $event->request->uri != $event->response->location)
			{
				return;
			}

			$location = '/admin/' . $target->module->id;

			switch ($mode)
			{
				case OPERATION_SAVE_MODE_CONTINUE:

					$location .= '/' . $event->rc['key'] . '/' . RouteMaker::ACTION_EDIT;

					break;

				case OPERATION_SAVE_MODE_NEW:

					$location .= '/' . RouteMaker::ACTION_NEW;

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
	 * Adds the `templates` directory to the template resolver paths.
	 *
	 * @param TemplateResolver\AlterEvent $event
	 * @param TemplateResolver $target
	 */
	static public function on_template_resolver_alter(TemplateResolver\AlterEvent $event, TemplateResolver $target)
	{
		if ($target instanceof TemplateResolverDecorator)
		{
			$target = $target->find_renderer(BasicTemplateResolver::class);
		}

		if (!$target instanceof BasicTemplateResolver)
		{
			return;
		}

		$target->add_path(DIR . 'templates');
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

		if (!$request->is_get || !preg_match('#\/admin\/([^\/\?]+)#', $request->uri, $matches))
		{
			return;
		}

		$category = $matches[1];
		$app = self::app();
		$routes = $app->routes;

		foreach ($app->modules->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if ($descriptor[Descriptor::CATEGORY] != $category)
			{
				continue;
			}

			$route_id = "admin:$module_id:index";

			if (!isset($routes[$route_id]))
			{
				continue;
			}

			$event->response = new RedirectResponse(self::app()->url_for($route_id), Status::TEMPORARY_REDIRECT, [

				'X-ICanBoogie-Redirected' => __FILE__ . '@' . __LINE__

			]);
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
	 * with the class of the {@link \Icybee\Element\Document} instance.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return string
	 */
	static public function markup_body(array $args, $engine, $template)
	{
		return '<body class="' . trim($args['class'] . ' ' . self::app()->document->css_class) . '">' . $engine($template) . '</body>';
	}

	/*
	 *
	 */

	/**
	 * Exception handler.
	 *
	 * @param \Exception $exception
	 */
	static public function exception_handler(/*\Exception */$exception)
	{
		$app = self::app();
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
			header('Cache-Control: Cache-Control: no-cache, no-store, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');
			header('X-ICanBoogie-Exception: ' . \ICanBoogie\strip_root($exception->getFile()) . '@' . $exception->getLine());
		}

		$formatted_exception = Debug::format_alert($exception);
		$reported = false;

		if (!($exception instanceof HTTPError))
		{
			Debug::report($formatted_exception);

			$reported = true;
		}

		if (!headers_sent() && PHP_SAPI != 'cli')
		{
			$site = isset($app->site) ? $app->site : null;

			if (class_exists('Brickrouge\Document'))
			{
				$css = [

					\Brickrouge\Document::resolve_url(\Brickrouge\ASSETS . 'brickrouge.css'),
					\Brickrouge\Document::resolve_url(ASSETS . 'admin.css'),
					\Brickrouge\Document::resolve_url(ASSETS . 'admin-more.css')

				];
			}
			else
			{
				$css = [];
			}

			ob_start();

			require DIR . 'templates/exception.phtml';

			$formatted_exception = ob_get_clean();
		}

		if (PHP_SAPI == 'cli')
		{
			$formatted_exception = strip_tags($formatted_exception);
		}

		echo $formatted_exception;
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
