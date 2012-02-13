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
use Brickrouge\Element;

class Hooks
{
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

	static public function before_icybee_render(Event $event)
	{
		global $core;

		if ($_POST || !$core->vars['enable_pages_cache'])
		{
			return;
		}

		$constructor = $event->constructor;
		$data = $event->constructor_data;

		$key = sprintf('%08x-%08x-%s', $core->site_id, (int) $core->user_id, sha1($event->uri));

		$cache = new FileCache
		(
			array
			(
				FileCache::T_COMPRESS => false,
				FileCache::T_REPOSITORY => $core->config['repository.cache'] . '/pages'
			)
		);

		// TODO-20110601: refactor this, the rendered data should be saved on the
		// 'Icybee::render' event.

		$event->rc = $cache->load($key, $constructor, $data);

		#
		# pages with form on them are not cached
		#

		foreach ($core->modules as $module_id => $module)
		{
			if ($module_id == 'forms')
			{
				$cache->delete($key);

				echo t('cache was disabled because the "forms" module has been loaded');

				return;
			}
		}
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
		global $page;

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
		global $page;

		$id = $args['id'];
		$rc = null;

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
	static public function markup_breadcrumb(array $args, \WdPatron $patron, $template)
	{
		global $page;

		return (string) new Element\Breadcrumb
		(
			array
			(
				Element\Breadcrumb::T_PAGE => $page
			)
		);
	}

	/**
	 * Alters cache collection to add pages cache manager.
	 *
	 * @param Event $event
	 * @param \ICanBoogie\Modules\System\Cache\Collection $collection
	 */
	public static function on_alter_cache_collection(Event $event, \ICanBoogie\Modules\System\Cache\Collection $collection)
	{
		$event->collection['contents.pages'] = new CacheManager;
	}
}