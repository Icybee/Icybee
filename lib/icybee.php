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

use ICanBoogie\ActiveRecord\Page;

use ICanBoogie\AuthenticationRequired;

define('Icybee\Pagemaker\CSS_DOCUMENT_PLACEHOLDER', uniqid());
define('Icybee\Pagemaker\JS_DOCUMENT_PLACEHOLDER', uniqid());

use ICanBoogie\ActiveRecord;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Operation;
use ICanBoogie\Route;
use ICanBoogie\I18n\Translator\Proxi;

use Brickrouge\Alert;

class Pagemaker
{
	public function run(Request $request, Response $response)
	{
		global $core;

		try
		{
			$page = $this->resolve_request($request);

			if ($page instanceof Response)
			{
				return $page;
			}

			return $this->run_callback($page, $request);
		}
		catch (\Exception $e)
		{
			$code = $e->getCode();
			$path = \ICanBoogie\DOCUMENT_ROOT . "protected/all/templates/$code.html";

			if (file_exists($path))
			{
				$template = file_get_contents($path);
				$patron = new \WdPatron();
				$page = \ICanBoogie\ActiveRecord\Page::from
				(
					array
					(
						'siteid' => $core->site_id,
						'title' => t($e->getTitle(), array(), array('scope' => 'exception')),
						'body' => t($e->getMessage(), array(), array('scope' => 'exception'))
					)
				);

				$request->context->page = $page;

				$response->status = $code;

				return $patron($template, $page);
			}

			throw $e;
		}
	}

	public function run_callback(Page $page, Request $request)
	{
		global $core;

		$time_start = microtime(true);

		// FIXME: because set() doesn't handle global vars ('$') correctly,
		// we have to set '$page' otherwise, a new variable '$page' is created

		$request->context->page = $page;
		$this->context['$page'] = $page;
		$this->context['this'] = $page;

		#

		new Pagemaker\BeforeRenderEvent
		(
			$this, array
			(
				'request' => $request,
				'page' => $page
			)
		);

		#
		# The page body is rendered before the template is parsed.
		#

		/*
		try
		*/
		{
			$body = $page->body ? $page->body->render() : '';
		}
		/*
		catch (Exception\HTTP $e)
		{
			$e->alter_header();
			$body = $e->getMessage();
		}
		catch (\Exception $e)
		{
			header('HTTP/1.0 ' . $e->getCode() . ' ' . str_replace("\n", " ", strip_tags($e->getMessage())));

			$body = Debug::format_alert($e);

			Debug::report($body);
		}
		*/

		$template = $this->resolve_template($page->template);
		$engine = $this->resolve_engine($template);

		$html = $engine($template, $page, array('file' => $page->template));


		#
		# editables
		#

		$admin_menu = $this->get_admin_menu();

		if ($admin_menu)
		{
			$html = str_replace('</body>', $admin_menu . '</body>', $html);
		}

		#

		new Pagemaker\RenderEvent
		(
			$this, array
			(
				'request' => $request,
				'page' => $page,
				'html' => &$html
			)
		);

		#
		# late replace
		#

		$document = $core->document;

		/*
		$markup = '<!-- $document.css -->';
		$pos = strpos($html, $markup);

		if ($pos !== false)
		{
			$html = substr($html, 0, $pos) . $document->css . substr($html, $pos + strlen($markup));
		}
		else
		{
			$html = str_replace('</head>', PHP_EOL . PHP_EOL . $document->css . PHP_EOL . '</head>', $html);
		}
		*/

		$markup = '<!-- $document.js -->';
		$pos = strpos($html, $markup);

		if ($pos !== false)
		{
			$html = substr($html, 0, $pos) . $document->js . substr($html, $pos + strlen($markup));
		}
		else
		{
			$html = str_replace('</body>', PHP_EOL . PHP_EOL . $document->js . PHP_EOL . '</body>', $html);
		}

		/*DIRTY
		$html .= $this->render_stats($time_start);
		*/

		$response = new Response
		(
			200, array
			(
				'Content-Type' => 'text/html; charset=utf-8'
			),

			$html
		);

		$response->cache_control = 'public';

		foreach ($core->modules as $module_id => $module)
		{
			if ($module_id == 'forms')
			{
				$cacheable = 'no-cache';

				$response->cache_control = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
			}
		}

		return $response;
	}

	/**
	 * Resolves a request into a page.
	 *
	 * @param Request $request
	 * @throws Exception\HTTP
	 *
	 * @return ICanBoogie\ActiveRecord\Page
	 */
	protected function resolve_request(Request $request)
	{
		global $core;

		$site = $request->context->site;

		if (!$site->siteid)
		{
			throw new Exception\HTTP('Unable to find matching website.', array(), 404);
		}

		$status = $site->status;

		if ($status == 2)
		{
			throw new Exception\HTTP
			(
				'The website is currently down for maintenance.', array(), 503
			);
		}
		else if ($status == 3)
		{
			throw new Exception\HTTP
			(
				'Access to the requested URL %uri is forbidden.', array
				(
					'uri' => $request->uri
				),

				403
			);
		}

		$user = $core->user;
		$path = $request->path;

		/*DIRTY
		if ($status != 1 && $path == '/' && $user->is_guest)
		{
			// TODO-20111101: we could use $core->site to get the current site, still we need to
			// check user permission to access the site. Using find_by_request() with the user
			// we get a site that the user can actually access, this is required to redirect the
			// user to the first available site when '/' is requested.

			$redirect_site = \ICanBoogie\Modules\Sites\Model::find_by_request($request, $user);

			if ($redirect_site->siteid != $site->siteid)
			{
				header('Location: ' . $redirect_site->url);

				exit;
			}

			throw new AuthenticationRequired
			(
				'The requested URL %url requires authentication.', array
				(
					'url' => $path
				)
			);
		}
		*/

		$page = $core->models['pages']->find_by_path($request->path);
		$query_string = $request->query_string;

		if ($page)
		{
			if ($page->location)
			{
				return new Response
				(
					301, array
					(
						'Location' => $page->location->url,
						'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
					)
				);
			}

			#
			# We make sure that a normalized URL is used. For instance, "/fr" is redirected to
			# "/fr/".
			#

			$parsed_url_pattern = Route::parse($page->url_pattern);

			if (!$parsed_url_pattern[1] && $page->url != $path)
			{
				return new Response
				(
					301, array
					(
						'Location' => $page->url . ($query_string ? '?' . $query_string : ''),
						'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
					)
				);
			}
		}

		if (!$page)
		{
			throw new Exception\HTTP('The requested URL was not found on this server.', array(), 404);
		}
		else if (!$page->is_online || $page->site->status != 1)
		{
			#
			# Offline pages are displayed if the user has ownership, otherwise an HTTP exception
			# with code 401 (Authentication) is thrown. We add the "✎" marker to the title of the
			# page to indicate that the page is offline but displayed as a preview for the user.
			#

			if (!$user->has_ownership('pages', $page))
			{
				throw new AuthenticationRequired
				(
					'The requested URL %url requires authentication.', array
					(
						'url' => $path
					)
				);
			}

			$page->title .= ' ✎';
		}

		if (isset($page->url_variables))
		{
			$_REQUEST += $page->url_variables; // TODO-20120313: this is for compat, but we shouldn't use $_REQUEST anymore.

			$request->path_params = $page->url_variables + $request->path_params;

			#
			# we unset the request params, it will be reconstructed on the next access.
			#

			unset($request->params);
		}

		return $page;
	}

	protected function resolve_template($name)
	{
		global $core;

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$pathname = $core->site->resolve_path('templates/' . $name);

		if (!$pathname)
		{
			throw new Exception('Unable to resolve path for template: %template', array('%template' => $pathname));
		}

		return file_get_contents($root . $pathname, true);
	}

	protected function resolve_engine($template)
	{
		$engine = new \WdPatron;

		return $engine;
	}

	protected function get_admin_menu()
	{
		global $core;

		if (!$core->user_id || $core->user instanceof ActiveRecord\Users\Members)
		{
			return;
		}

		$document = $core->document;
		$document->css->add(ASSETS . 'css/admin-menu.css');

		$user = $core->user;
		$page = $core->request->context->page;

		$contents = null;
		$edit_target = $page->node ?: $page;

		if (!$edit_target)
		{
			#
			# when the page is cached, 'page' is null because it is not loaded, we should load
			# the page ourselves to present the admin menu on cached pages.
			#

			return;
		}

		$translator = new Proxi();

		if ($user->language)
		{
			$translator->language = $user->language;
		}

		$contents .= '<ul style="text-align: center;"><li>';

		if ($user->has_permission(Module::PERMISSION_MAINTAIN, $edit_target->constructor))
		{
			$contents .= '<a href="' . $core->site->path . '/admin/' . $edit_target->constructor . '/' . $edit_target->nid . '/edit' . '" title="' . $translator('Edit: !title', array('!title' => $edit_target->title)) . '">' . $translator('Edit') . '</a> &ndash; ';
		}

		$contents .= '<a href="' . \ICanBoogie\escape(Operation::encode('users/logout', array('location'  => $_SERVER['REQUEST_URI']))) . '">' . $translator('Disconnect') . '</a> &ndash;
		<a href="' . $core->site->path . '/admin/">' . $translator('Admin') . '</a></li>';
		$contents .= '</ul>';

		#
		# configurable
		#

		$routes = $core->routes;

		$links = array();
		$site = $core->site;

		foreach ($core->modules as $module_id => $module)
		{
			$id = "admin:$module_id/config";

			if (empty($routes[$id]))
			{
				continue;
			}

			$pathname = $routes[$id]->pattern;

			$links[] = '<a href="' . \ICanBoogie\escape(Route::contextualize($pathname)) . '">' . $module->title . '</a>';
		}

		if ($links)
		{
			$contents .= '<div class="panel-section-title">Configurer</div>';
			$contents .= '<ul><li>' . implode('</li><li>', $links) . '</li></ul>';
		}

		#
		#
		#

		$editables_by_category = array();
		$descriptors = $core->modules->descriptors;

		$nodes = array();

		foreach (self::$nodes as $node)
		{
			$nodes[$node->nid] = $node;
		}

		$translator->scope = 'module_category';

		foreach ($nodes as $node)
		{
			if ($node->nid == $edit_target->nid || !$user->has_permission(Module::PERMISSION_MAINTAIN, $node->constructor))
			{
				continue;
			}

			// TODO-20101223: use the 'language' attribute whenever available to translate the
			// categories in the user's language.

			$category = isset($descriptors[$node->constructor][Module::T_CATEGORY]) ? $descriptors[$node->constructor][Module::T_CATEGORY] : 'contents';
			$category = $translator($category);

			$editables_by_category[$category][] = $node;
		}

		$translator->scope = null;

		foreach ($editables_by_category as $category => $nodes)
		{
			$contents .= '<div class="panel-section-title">' . \ICanBoogie\escape($category) . '</div>';
			$contents .= '<ul>';

			foreach ($nodes as $node)
			{
				$contents .= '<li><a href="' . Route::contextualize('/admin/' . $node->constructor . '/' . $node->nid . '/edit') . '" title="' . $translator->__invoke('Edit: !title', array('!title' => $node->title)) . '">' . \ICanBoogie\escape(\ICanBoogie\shorten($node->title)) . '</a></li>';
			}

			$contents .= '</ul>';
		}

		$rc = '';

		if ($contents)
		{
			$rc  = <<<EOT
<div id="icybee-admin-menu">
<div class="panel-title">Icybee</div>
<div class="contents">$contents</div>
</div>
EOT;
		}

		return $rc;
	}

	static protected $nodes = array();

	public static function on_nodes_load(Event $event)
	{
		$nodes = $event->nodes;

		foreach ($nodes as $node)
		{
			if (!$node instanceof ActiveRecord\Node)
			{
				throw new Exception('Not a node object: \1', array($node));
			}
		}

		self::$nodes = array_merge(self::$nodes, $event->nodes);
	}

	/*DIRTY
	protected function render_stats($time_start)
	{
		global $core;

		$time_end = microtime(true);
		$time = $time_end - $time_start;

		$queries_count = 0;
		$queries_stats = array();

		$profiling = null;
		$dbtime = 0;

		foreach ($core->connections as $id => $connection)
		{
			$count = $connection->queries_count;
			$queries_count += $count;
			$queries_stats[] = $id . ': ' . $count;

			foreach ($connection->profiling as $note)
			{
				$dbtime += $note[0];
				$profiling .= number_format($note[0], 6, '.', ' ') . ': ' . $note[1] . PHP_EOL;
			}
		}

		if ($core->user_id != 1)
		{
			$profiling = null;
		}

		return '<!-- ' . \ICanBoogie\format
		(
			'icybee v:version - in :elapsed ms (icanboogie: :icanboogie_elapsed ms, rendering: :rendering_elapsed ms, db: :dbtime ms), using :memory-usage (peak: :memory-peak), :queries-count queries (:queries-details)', array
			(
				':version' => \Icybee\VERSION,
				':elapsed' => number_format(($time_end - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
				':icanboogie_elapsed' => number_format(($_SERVER['ICANBOOGIE_READY_TIME_FLOAT'] - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
				':rendering_elapsed' => number_format($time * 1000, 2, '.', ''),
				':dbtime' => number_format($dbtime * 1000, 2, '.', ''),
				':memory-usage' => number_format(memory_get_usage() / (1024 * 1024), 3) . 'Mb',
				':memory-peak' => number_format(memory_get_peak_usage() / (1024 * 1024), 3) . 'Mb',
				':queries-count' => $queries_count,
				':queries-details' => $queries_stats ? implode(', ', $queries_stats) : 'none'
			)
		)

		. ($profiling ? PHP_EOL . PHP_EOL . $profiling . PHP_EOL : '')

		. ' -->' . PHP_EOL;
	}
	*/
}

namespace Icybee\Pagemaker;

/**
 * Event class for the 'Icybee\Pagemaker::render:before'.
 */
class BeforeRenderEvent extends \ICanBoogie\Event
{
	/**
	 * Request.
	 *
	 * @var \ICanBoogie\HTTP\Request
	 */
	public $request;

	/**
	 * Response.
	 *
	 * @var \ICanBoogie\HTTP\Response
	 */
	public $response;

	/**
	 * Constructor for the page.
	 *
	 * @var \callable
	 */
	public $constructor;

	/**
	 * Reference to an empty variable that can be altered to put the rendered HTML.
	 *
	 * @var string
	 */
	public $html;

	public function __construct(\Icybee\Pagemaker $target, array $properties)
	{
		parent::__construct($target, 'render:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Pagemaker::render` event.
 */
class RenderEvent extends \ICanBoogie\Event
{
	/**
	 * The request.
	 *
	 * @var \ICanBoogie\HTTP\Request
	 */
	public $request;

	/**
	 * The page being rendered.
	 *
	 * @var \ICanBoogie\ActiveRecord\Page
	 */
	public $page;

	/**
	 * The rendered HTML.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the `render` type.
	 *
	 * @param \Icybee $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Pagemaker $target, array $properties)
	{
		parent::__construct($target, 'render', $properties);
	}
}