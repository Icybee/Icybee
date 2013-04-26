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
use ICanBoogie\Event;
use ICanBoogie\FileCache;
use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;

use Brickrouge\Element;

use Icybee\Modules\Files\File;
use Icybee\Modules\Sites\Site;

class Hooks
{
	static public function on_http_dispatcher_collect(Dispatcher\CollectEvent $event, Dispatcher $target)
	{
		$event->dispatchers['pages'] = function(Request $request)
		{
			global $core;

			require_once \ICanBoogie\DOCUMENT_ROOT . 'user-startup.php';

			$response = new Response();
			$controller = new PageController();

			$rc = $controller($request, $response);

			if ($rc instanceof Response)
			{
				$response = $rc;
			}
			else
			{
				$response->body = $rc;
			}

			/*
			if ($core->user->is_guest && $request->is_get)
			{
				$response->cache_control = 'public';
				$response->expires = '+7 days';
			}
			*/

			$response->cache_control = 'private, no-cache, no-store, must-revalidate';

			return $response;
		};
	}

	/**
	 * The callback is called when the `Icybee\Modules\Files\File::move` event is triggered,
	 * allowing us to update content to the changed path of resource.
	 *
	 * @param File\MoveEvent $event
	 * @param File $target
	 */
	static public function on_file_move(File\MoveEvent $event, File $target)
	{
		global $core;

		$core->models['pages/contents']->execute
		(
			'UPDATE {self} SET content = REPLACE(content, ?, ?)', array($event->from, $event->to)
		);
	}

	/**
	 * The callback is called when the `Icybee\Modules\Pages\Page::move` event is triggered,
	 * allowing us to update content to the changed url of the page.
	 *
	 * Note that *only* url within something that looks like a HTML attribute are updated, the
	 * matching pattern is ~="<url>("|/)~
	 *
	 * @param Page\MoveEvent $event
	 * @param Page $target
	 */
	static public function on_page_move(Page\MoveEvent $event, Page $target)
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

		foreach ($model->where('content LIKE ?', '%' . $old . '%') as $record)
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
	static public function get_page(\ICanBoogie\Core $core)
	{
		return $core->request->context->page;
	}

	/**
	 * Returns the home page of the target site.
	 *
	 * @param \Icybee\Modules\Sites\Site $site
	 *
	 * @return \Icybee\Modules\Pages\Page|null The home page of the target site or null if there is
	 * none.
	 */
	static public function get_home(Site $site)
	{
		global $core;

		return $core->models['pages']->find_home($site->siteid);
	}

	static public function before_document_render_title(\Icybee\Document\BeforeRenderTitleEvent $event)
	{
		global $core;

		$page = $core->request->context->page;

		$event->separator = ' − ';
		$event->title = $page->title . $event->separator . $page->site->title;
	}

	/*
	 * Markups
	 */

	static public function markup_page_region(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$id = $args['id'];
		$page = $core->request->context->page;
		$element = new Element('div', array('id' => $id, 'class' => "region region-$id"));
		$html = null;

		new Page\RenderRegionEvent
		(
			$page, array
			(
				'id' => $id,
				'page' => $page,
				'element' => $element,
				'html' => &$html
			)
		);

		if (!$html)
		{
			return;
		}

		$element[Element::INNER_HTML] = $html;

		return $element;
	}

	static public function markup_page_title(array $args, $engine, $template)
	{
		global $core;

		$page = $core->request->context->page;
		$title = $page->title;
		$html = \ICanBoogie\escape($title);

		new Page\RenderTitleEvent($page, array('title' => $title, 'html' => &$html));

		return $template ? $engine($template, $html) : $html;
	}

	/**
	 * Defines an editable page content in a template.
	 *
	 * <pre>
	 * <p:page:content
	 *     id = qname
	 *     title = string
	 *     editor = string
	 *     inherit = boolean>
	 *     <!-- Content: with-param*, template? -->
	 * </p:page:content>
	 * </pre>
	 *
	 * The `id` attribute specifies the identifier of the content, it is required and must be
	 * unique in the template. The `title` attribute specifies the label of the editor in the
	 * page editor, it is required. The `editor` attribute specifies the editor to use to edit
	 * the content, it is optional. The `inherit` attribute specifies that the content can be
	 * inherited.
	 *
	 * Example:
	 *
	 * <pre>
	 * <p:page:content id="body" title="Page body" />
	 *
	 * <p:page:content id="picture" title="Decorating picture" editor="image" inherit="inherit">
	 * <img src="#{@path}" alt="#{@alt}" />
	 * </p>
	 *
	 * </pre>
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

namespace Icybee\Modules\Pages\Page;

use Icybee\Modules\Pages\Page;

class RenderTitleEvent extends \ICanBoogie\Event
{
	/**
	 * Title of the page.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Reference to the rendered title of the page.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_title`.
	 *
	 * @param Page $target
	 * @param array $payload
	 */
	public function __construct(Page $target, array $payload)
	{
		parent::__construct($target, 'render_title', $payload);
	}
}

class RenderRegionEvent extends \ICanBoogie\Event
{
	/**
	 * Identifier of the region.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Page where the region is rendered.
	 *
	 * @var Page
	 */
	public $page;

	/**
	 * The region element.
	 *
	 * @var Element
	 */
	public $element;

	/**
	 * Reference to the rendered HTML of the region.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_region`.
	 *
	 * @param Page $target
	 * @param array $payload
	 */
	public function __construct(Page $target, array $payload)
	{
		parent::__construct($target, 'render_region', $payload);
	}
}