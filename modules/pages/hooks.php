<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use ICanBoogie\ActiveRecord;
use Icybee\Modules\Sites\Site;
use ICanBoogie\Event;
use ICanBoogie\FileCache;
use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;

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
	static public function on_urlchange(Event $event, Page $target)
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
	 * An operation (save, delete, online, offline) has invalidated the cache, thus we have to
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
	 * @return \Icybee\Modules\Pages\Page
	 */
	public static function get_page(\ICanBoogie\Core $core)
	{
		return $core->request->context->page;
	}

	/**
	 * Returns the home page of the target site.
	 *
	 * @param Icybee\Modules\Sites\Site $site
	 *
	 * @return Icybee\Modules\Pages\Page|null The home page of the target site or null if there is
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

	public static function markup_page_region(array $args, \Patron\Engine $patron, $template)
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

	public static function markup_page_title(array $args, $engine, $template)
	{
		global $core;

		$page = $core->request->context->page;
		$title = $page->title;
		$html = \ICanBoogie\escape($title);

		Event::fire('render_title', array('title' => $title, 'html' => &$html), $page);

		return $template ? $engine($template, $html) : $html;
	}

	/**
	 * The `wdp:content` markup defines editable content zones in a page template.
	 *
	 * @param array $args
	 * @param \Patron\Engine $patron
	 * @param mixed $template
	 *
	 * @return mixed
	 */
	static public function markup_page_content(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$render = $args['render'];

		if ($render == 'none')
		{
			return;
		}

		$page = $core->request->context->page;
		$pageid = $page->nid;
		$contentid = $args['id'];
		$contents = array_key_exists($contentid, $page->contents) ? $page->contents[$contentid] : null;

		if (!$contents && !empty($args['inherit']))
		{
			$node = $page->parent;

			while ($node)
			{
				$node_contents = $node->contents;

				if (empty($node_contents[$contentid]))
				{
					$node = $node->parent;

					continue;
				}

				$contents = $node_contents[$contentid];

				break;
			}

			#
			# maybe the home page define the contents, but because the home page is not the parent
			# of pages on single language sites, we have to check it now.
			#

			if (!$contents)
			{
				$node_contents = $page->home->contents;

				if (isset($node_contents[$contentid]))
				{
					$contents = $node_contents[$contentid];
				}
			}
		}

		$editor = null;
		$rendered = null;

		if ($contents)
		{
			$editor = $contents->editor;
			$rendered = $contents->render();
		}

		if (!$rendered)
		{
			return;
		}

		$element = new Element
		(
			'div', array
			(
				'id' => 'content-' . $contentid,
				'class' => 'editor-' . \ICanBoogie\normalize($editor)
			)
		);

		if (version_compare(PHP_VERSION, '5.3.4', '>='))
		{
			$patron->context['self']['element'] = $element;
		}
		else // COMPAT
		{
			$self = $patron->context['self'];
			$self['element'] = $element;
			$patron->context['self'] = $self;
		}

		$rc = $template ? $patron($template, $rendered) : $rendered;

		if (!$rc)
		{
			return;
		}

		if (preg_match('#\.html$#', $page->template) && empty($args['no-wrapper']))
		{
			$element[Element::INNER_HTML] = $rc;
			$rc = $element;
		}

		$rc = self::handle_external_anchors($rc);

		return $rc;
	}

	/**
	 * Adds a blank target to external href.
	 *
	 * @param string $html
	 */
	static protected function handle_external_anchors($html)
	{
		return preg_replace_callback
		(
			'#<a\s+[^>]+>#', function($matches)
			{
				$str = array_shift($matches);

				preg_match_all('#([a-zA-Z0-9\-]+)\="([^"]+)#', $str, $matches, 0, PREG_SET_ORDER);

				if (empty($matches[1]))
				{
					return $str;
				}

				$attributes = array_combine($matches[1], $matches[2]);

				if (isset($attributes['href']))
				{
					if (preg_match('#^http(s)?://#', $attributes['href'], $m))
					{
						$attributes['target'] = '_blank';
					}
				}

				$str = '<a';

				foreach ($attributes as $attribute => $value)
				{
					$str .= ' ' . $attribute . '="' . $value . '"';
				}

				$str .= '>';

				return $str;
			},

			$html
		);
	}
}