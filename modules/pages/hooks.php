<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Site;
use ICanBoogie\Event;
use ICanBoogie\FileCache;
use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Operation\ProcessEvent;

use Brickrouge\Element;

class Hooks
{
	static public function on_http_dispatcher_populate(Dispatcher\PopulateEvent $event, Dispatcher $target)
	{
		$event->controllers['pages'] = function(Request $request)
		{
			global $core;

			require_once \ICanBoogie\DOCUMENT_ROOT . 'user-startup.php';

			$response = new Response();
			$controller = new PageController;

			$rc = $controller($request, $response);

			if ($rc instanceof Response)
			{
				return $rc;
			}

			$response->body = $rc;

			/*
			$response->cache_control->cacheable = 'public';
			$response->expires = '+7 days';
			*/

			return $response;
		};
	}

	/**
	 * Updates view targets.
	 *
	 * @param ProcessEvent $event
	 * @param SaveOperation $operation
	 */
	static public function on_save(ProcessEvent $event, SaveOperation $operation)
	{
		global $core;

		$request = $event->request;
		$contents = $request['contents'];
		$editor_ids = $request['editors'];
		$nid = $event->response->rc['key'];

		if ($editor_ids)
		{
			foreach ($editor_ids as $content_id => $editor_id)
			{
				if ($editor_id != 'view')
				{
					continue;
				}

				if (empty($contents[$content_id]))
				{
					// TODO-20120811: should remove view reference

					continue;
				}

				$content = $contents[$content_id];

				if (strpos($content, '/') !== false)
				{
					$view_target_key = 'views.targets.' . strtr($content, '.', '_');

					$core->site->metas[$view_target_key] = $nid;
				}
			}
		}
	}

	/**
	 * The callback is called when the `resources.files.path.change` is triggered, allowing us to
	 * update content to the changed path of resource.
	 *
	 * @param Event $event
	 */
	static public function resources_files_path_change(Event $event)
	{
		global $core;

		try
		{
			$model = $core->models['pages/contents'];
			$path = $event->path;

			if ($path)
			{
				$model->execute
				(
					'UPDATE {self} SET content = REPLACE(content, ?, ?)', $path
				);
			}
		}
		catch (\Exception $e) { return; }
	}

	/**
	 * The callback is called when the `urlchange` event is triggered by a Page activerecord,
	 * allowing us to update content to the changed url of the page.
	 *
	 * Note that *only* url within something that looks like a HTML attribute are updated, the
	 * matching pattern is ~="<url>("|/)~
	 *
	 * @param Event $event
	 */
	static public function on_urlchange(Event $event, ActiveRecord\Page $sender)
	{
		global $core;

		try
		{
			$model = $core->models['pages/contents'];
		}
		catch (\Exception $e) { return; }

		$old = $event->from;
		$new = $event->to;

		if (!$old)
		{
			return;
		}

		$records = $model->where('content LIKE ?', '%' . $old . '%')->all;

		foreach ($records as $record)
		{
			$content = $record->content;
			$content = preg_replace('~=\"' . preg_quote($old, '~') . '(\"|\/)~', '="' . $new . '$1', $content);

			if ($content == $record->content)
			{
				continue;
			}

			$model->execute
			(
				'UPDATE {self} SET content = ? WHERE pageid = ? AND contentid = ?', array
				(
					$content, $record->pageid, $record->contentid
				)
			);
		}
	}

	/**
	 * An operation (save, delete, online, offline) has invalidated the cache, this we have to
	 * reset it.
	 */
	static public function invalidate_cache()
	{
		global $core;

		$cache = new FileCache
		(
			array
			(
				FileCache::T_REPOSITORY => $core->config['repository.cache'] . '/pages'
			)
		);

		return $cache->clear();
	}

	/**
	 * Returns the current page.
	 *
	 * This getter is a shortcut for the `request->context->page` property.
	 *
	 * @param \ICanBoogie\Core $core
	 *
	 * @return \ICanBoogie\ActiveRecord\Page
	 */
	public static function core__volatile_get_page(\ICanBoogie\Core $core)
	{
		return $core->request->context->page;
	}

	/**
	 * Returns the home page of the target site.
	 *
	 * @param ICanBoogie\ActiveRecord\Site $site
	 *
	 * @return ICanBoogie\ActiveRecord\Page|null The home page of the target site or null if there is
	 * none.
	 */
	public static function get_home(Site $site)
	{
		global $core;

		return $core->models['pages']->find_home($site->siteid);
	}

	public static function on_document_render_title(Event $event)
	{
		global $core;

		$page = $core->request->context->page;

		$event->separator = ' − ';
		$event->title = $page->title . $event->separator . $page->site->title;
	}

	/**
	* Returns the translations available for a page.
	*
	* @param WdHook $hook
	* @param WdPatron $patron
	* @param unknown_type $template
	*
	* @return string
	*/
	public static function markup_page_translations(array $args, \WdPatron $patron, $template)
	{
		$page = $args['select'];
		$page_language = $page->language;

		if (!$page_language)
		{
			return;
		}

		$translations = $page->translations;

		if (!$translations)
		{
			return;
		}

		foreach ($translations as $i => $translation)
		{
			if ($translation->is_accessible)
			{
				continue;
			}

			unset($translations[$i]);
		}

		if (!$translations)
		{
			return;
		}

		if (!$template)
		{
			$template = <<<EOT
<div id="page-translations">
<ul>
	<wdp:foreach>
	<li><a href="#{@url}">#{@language}</a></li>
	</wdp:foreach>
</ul>
</div>
EOT;
		}

		return $patron($template, $translations);
	}

	public static function markup_page_region(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$id = $args['id'];
		$rc = null;
		$page = $core->request->context->page;

		Event::fire
		(
			'markup.page_region.render_inner_html', array
			(
				'id' => $id,
				'page' => $page,
				'rc' => &$rc
			)
		);

		if (!$rc)
		{
			return;
		}

		return '<div id="region-' . $id . '" class="region region-' . $id . '">' . $rc . '</div>';
	}

	/**
	 * Returns the breadcrumb for the current page.
	 *
	 * The breadcrumb is build and rendered using the #{@link \Brickrouge\Element\Breadcrumb}
	 * element.
	 *
	 * @param array $args
	 * @param \WdPatron $patron
	 * @param array|string $template
	 *
	 * @return string
	 */
	public static function markup_breadcrumb(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$page = $core->request->context->page;

		return (string) new BreadcrumbElement
		(
			array
			(
				BreadcrumbElement::PAGE => $page
			)
		);
	}

	public static function markup_navigation_leaf(array $args, $patron, $template)
	{
		global $core;

		$page = $core->request->context->page;

		return (string) new NavigationBranchElement($page);
	}

	/**
	 * Calls an renders the specified view.
	 *
	 * @param array $args
	 * @param WdPatron $patron
	 * @param mixed $template
	 *
	 * @return mixed
	 */
	public static function markup_call_view(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$editor = $core->editors['view'];

		// TODO-20101216: The view should handle parsing template or not

		return $render = $editor->render($args['name'], $patron, $template);

		return $template ? $patron($template, $render) : $render;
	}

	public static function markup_page_title(array $args, $engine, $template)
	{
		global $core;

		$page = $core->request->context->page;
		$title = $page->title;
		$html = \ICanBoogie\escape($title);

		Event::fire('render_title', array('title' => $title, 'html' => &$html), $page);

		return $template ? $engine($template, $html) : $html;
	}
}