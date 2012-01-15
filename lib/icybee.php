<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Route;
use ICanBoogie\I18n\Translator\Proxi;

class Icybee extends WdPatron
{
	static public function get_singleton($class='Icybee')
	{
		return parent::get_singleton($class);
	}

	protected function search_templates()
	{
		global $core;

		if ($this->templates_searched)
		{
			return;
		}

		$templates = $core->site->partial_templates;

		foreach ($templates as $id => $path)
		{
			$this->addTemplate($id, '!f:' . $path);
		}

		$this->templates_searched = true;
	}

	public function run()
	{
		global $core, $wddebug_time_reference;

		$time_start = microtime(true);

// 		session_cache_limiter('public');

		$html = null;
		$request = $core->request;
		$constructor_data = array($request);

		try
		{
			Event::fire
			(
				'render:before', array
				(
					'request' => $request,
// 					'uri' => $_SERVER['REQUEST_URI'],
					'constructor' => array($this, 'run_callback'),
					'constructor_data' => &$constructor_data,
					'rc' => &$html
				),

				$this
			);

			if ($html === null)
			{
				$html = call_user_func_array(array($this, 'run_callback'), $constructor_data);
			}
		}
		catch (\Exception $e)
		{
			$code = $e->getCode();
			$path = ICanBoogie\DOCUMENT_ROOT . "protected/all/templates/$code.html";

			if (file_exists($path))
			{
				header('HTTP/1.1 ' . $e->getTitle());

				$template = file_get_contents($path);

				exit($this($template, $e));
			}

			throw $e;
		}

		$time_end = microtime(true);
		$time = $time_end - $time_start;

		#
		# stats
		#

		$queries_count = 0;
		$queries_stats = array();

		$profiling = null;

		foreach ($core->connections as $id => $connection)
		{
			$count = $connection->queries_count;
			$queries_count += $count;
			$queries_stats[] = $id . ': ' . $count;

			if ($core->user_id == 1)
			{
				foreach ($connection->profiling as $note)
				{
					$profiling .= number_format($note[0], 6, '.', ' ') . ': ' . $note[1] . PHP_EOL;
				}
			}
		}

		$comment = '<!-- ' . t
		(
			'icybee v:version (core: :core_version) # rendering time: :elapsed sec (global time: :framework_elapsed), memory usage :memory-usage (peak: :memory-peak), queries: :queries-count (:queries-details)', array
			(
				':core_version' => ICanBoogie\VERSION,
				':elapsed' => number_format($time, 3, '.', ''),
				':framework_elapsed' => number_format($time_end - $wddebug_time_reference, 3, '.', ''),
				':memory-usage' => memory_get_usage(),
				':memory-peak' => memory_get_peak_usage(),
				':queries-count' => $queries_count,
				':queries-details' => $queries_stats ? implode(', ', $queries_stats) : 'none',
				':version' => Icybee\VERSION
			)
		)

		. ($profiling ? PHP_EOL . PHP_EOL . $profiling . PHP_EOL : '')

		. ' -->' . PHP_EOL;

		echo $html . $comment;
	}

	public function run_callback(Request $request)
	{
		global $core, $page;

		$status = $core->site->status;

		if ($status != 1 && $core->user->is_guest)
		{
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

			// TODO-20111101: we could use $core->site to get the current site, still we need to
			// check user permission to access the site. Using find_by_request() with the user
			// we get a site that the user can actually access, this is required to redirect the
			// user to the first available site when '/' is requested.

			$site = \ICanBoogie\Modules\Sites\Hooks::find_by_request($request, $core->user);

			if ($site->siteid && $site->siteid != $core->site_id)
			{
				header('Location: ' . $site->url);

				exit;
			}

			throw new Exception\HTTP
			(
				'The requested URL %uri requires authentication.', array
				(
					'uri' => $request->uri
				),

				401
			);
		}

		$path = $request->path;
		$page = $this->find_page_by_uri($path, $request->query_string);

		if (!$page)
		{
			throw new Exception\HTTP
			(
				'The requested URL %uri was not found on this server.', array
				(
					'uri' => $request->uri
				),

				404
			);
		}
		else if (!$page->is_online)
		{
			#
			# Offline pages are displayed if the user has ownership, otherwise an HTTP exception
			# with code 401 (Authentication) is thrown. We add the "✎" marker to the title of the
			# page to indicate that the page is offline but displayed as a preview for the user.
			#

			if (!$core->user->has_ownership('pages', $page))
			{
				throw new Exception\HTTP
				(
					'The requested URL %uri requires authentication.', array
					(
						'uri' => $request->uri
					),

					401
				);
			}

			$page->title .= ' ✎';
		}

		$document = $core->document;

		// FIXME: because set() doesn't handle global vars ('$') correctly,
		// we have to set '$page' otherwise, a new variable '$page' is created

		$this->context['$page'] = $page;

		if (isset($page->url_variables))
		{
			$request = $core->request;

			$_REQUEST += $page->url_variables;

			$request->path_parameters = $page->url_variables + $request->path_parameters;

			#
			# we unset the request params, it will be reconstructed on the next access.
			#

			unset($request->params);
		}

		$_REQUEST += array
		(
			'page' => 0
		);

		$this->context['this'] = $page;

		#
		# The page body is rendered before the template is parsed.
		#

		try
		{
			$body = $page->body ? $page->body->render() : '';
		}
		catch (Exception\HTTP $e)
		{
			$e->alter_header();
			$body = $e->getMessage();
		}

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$file = $core->site->resolve_path('templates/' . $page->template);

		if (!$file)
		{
			throw new Exception('Unable to resolve path for template: %template', array('%template' => $page->template));
		}

		$template = file_get_contents($root . $file, true);

		$html = $this($template, $page, array('file' => $file));

		Event::fire
		(
			'render', array
			(
				'request' => $request,
				'page' => $page,
				'rc' => &$html
			),

			$this
		);

		#
		# editables
		#

		$admin_menu = $this->get_admin_menu();

		if ($admin_menu)
		{
			$html = str_replace('</body>', $admin_menu . '</body>', $html);
		}

		#
		# late replace
		#

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

		$markup = '<!-- $log -->';
		$pos = strpos($html, $markup);

		if ($pos !== false)
		{
			$html = substr($html, 0, $pos) . $this->get_log() . substr($html, $pos + strlen($markup));
		}

		return $html;
	}

	protected function find_page_by_uri($path, $query_string=null)
	{
		global $core;

		$page = $core->models['pages']->loadByPath($path);

		if ($page)
		{
			if ($page->location)
			{
				header('Location: ' . $page->location->url);

				exit;
			}

			$parsed_url_pattern = Route::parse($page->url_pattern);

			if (empty($parsed_url_pattern[1]) && $page->url != $path)
			{
				header('Location: ' . $page->url . ($query_string ? '?' . $query_string : ''), true, 301);

				exit;
			}
		}
		else if ($path == '/' && $core->site->path)
		{
			exit(__FILE__ . '@' . __LINE__);

			header('Location: ' . $core->site->url . ($query_string ? '?' . $query_string : ''));

			exit;
		}

		return $page;
	}

	protected function get_admin_menu()
	{
		global $core, $page;

		if (!$core->user_id || $core->user instanceof ActiveRecord\Users\Members)
		{
			return;
		}

		$document = $core->document;
		$document->css->add(Icybee\ASSETS . 'css/admin-menu.css');

		$user = $core->user;

		$contents = null;
		$edit_target = isset($page->node) ? $page->node : $page;

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
			$contents .= '<a href="' . $core->site->path . '/admin/' . $edit_target->constructor . '/' . $edit_target->nid . '/edit' . '" title="' . $translator->__invoke('Edit: !title', array('!title' => $edit_target->title)) . '">' . $translator->__invoke('Edit') . '</a> &ndash; ';
		}

		$contents .= '<a href="' . wd_entities(Operation::encode('users/logout', array('location'  => $_SERVER['REQUEST_URI']))) . '">' . $translator->__invoke('Disconnect') . '</a> &ndash;
		<a href="' . $core->site->path . '/admin/">' . $translator->__invoke('Admin') . '</a></li>';
		$contents .= '</ul>';


		#
		# configurable
		#

		$routes = $core->configs['admin_routes'];

		Route::add($routes);

		$links = array();
		$site = $core->site;

		foreach ($core->modules as $module_id => $module)
		{
			$route = '/admin/' . $module_id . '/config';

			if (!Route::find($route))
			{
				continue;
			}

			$links[] = '<a href="' . wd_entities($site->path . $route) . '">' . $module->title . '</a>';
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

		$translator->scope = 'module_category.title';

		foreach ($nodes as $node)
		{
			if ($node->nid == $edit_target->nid || !$user->has_permission(Module::PERMISSION_MAINTAIN, $node->constructor))
			{
				continue;
			}

			// TODO-20101223: use the 'language' attribute whenever available to translate the
			// categories in the user's language.

			$category = isset($descriptors[$node->constructor][Module::T_CATEGORY]) ? $descriptors[$node->constructor][Module::T_CATEGORY] : 'contents';
			$category = $translator->__invoke($category);

			$editables_by_category[$category][] = $node;
		}

		$translator->scope = null;

		foreach ($editables_by_category as $category => $nodes)
		{
			$contents .= '<div class="panel-section-title">' . wd_entities($category) . '</div>';
			$contents .= '<ul>';

			foreach ($nodes as $node)
			{
				$contents .= '<li><a href="' . $core->site->path . '/admin/' . $node->constructor . '/' . $node->nid . '/edit' . '" title="' . $translator->__invoke('Edit: !title', array('!title' => $node->title)) . '">' . wd_entities(wd_shorten($node->title)) . '</a></li>';
			}

			$contents .= '</ul>';
		}

		$rc = '';

		if ($contents)
		{
			$rc  = <<<EOT
<div id="wdpublisher-admin-menu">
<div class="panel-title">Icybee</div>
<div class="contents">$contents</div>
</div>
EOT;
		}

		return $rc;
	}

	protected function get_log()
	{
		global $core;

		$log_done = Debug::fetch_messages('done');
		$log_error = Debug::fetch_messages('error');
		$log_debug = Debug::fetch_messages('debug');

		if ($core->user_id != 1)
		{
			return;
		}

		$messages = array_merge($log_done, $log_error, $log_debug);

		if (!$messages)
		{
			return;
		}

		$log = '<div class="wdp-debug"><h6>icybee: debug</h6><ul>';

		foreach ($messages as $message)
		{
			$log .= '<li>' . $message . '</li>' . PHP_EOL;
		}

		$log .= '</ul></div>' . PHP_EOL;

		return $log;
	}

	static protected $nodes = array();

	static public function on_nodes_load(Event $event)
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
}