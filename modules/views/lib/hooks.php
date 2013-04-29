<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Operation;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Route;

use Icybee\Modules\Cache\Collection as CacheCollection;
use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Sites\Site;

class Hooks
{
	/*
	 * EVENTS
	 */

	/**
	 * Updates view targets.
	 *
	 * @param Operation\ProcessEvent $event
	 * @param \Icybee\Modules\Pages\SaveOperation $operation
	 */
	static public function on_page_save(Operation\ProcessEvent $event, \Icybee\Modules\Pages\SaveOperation $operation)
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
	 * Adds views cache manager to the cache collection.
	 *
	 * @param CacheCollection\CollectEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
	{
		$event->collection['icybee.views'] = new CacheManager;
	}

	/*
	 * PROTOTYPE
	 */

	static private $pages_model;
	static private $url_cache_by_siteid = array();

	/**
	 * Returns the relative URL of a record for a view type.
	 *
	 * @param ActiveRecord $target
	 * @param string $type View type.
	 *
	 * @return string
	 */
	static public function url(ActiveRecord $target, $type='view')
	{
		global $core;

		if (self::$pages_model === false)
		{
			#
			# we were not able to get the "pages" model in a previous call, we don't try again.
			#

			return '#';
		}
		else
		{
			try
			{
				self::$pages_model = $core->models['pages'];
			}
			catch (\Exception $e)
			{
				return '#';
			}
		}

		$constructor = isset($target->constructor) ? $target->constructor : $target->model->id;
		$constructor = strtr($constructor, '.', '_');

		$key = 'views.targets.' . $constructor . '/' . $type;
		$site_id = isset($target->siteid) ? $target->siteid : $core->site_id;

		if (isset(self::$url_cache_by_siteid[$site_id][$key]))
		{
			$pattern = self::$url_cache_by_siteid[$site_id][$key];
		}
		else
		{
			$pattern = false;
			$page_id = null;

			if ($site_id)
			{
				$site = $core->models['sites'][$site_id];
				$page_id = $site->metas[$key];

				if ($page_id)
				{
					$pattern = self::$pages_model[$page_id]->url_pattern;
				}
			}

			self::$url_cache_by_siteid[$site_id][$key] = $pattern;
		}

		if (!$pattern)
		{
			return '#uknown-target-for:' . $constructor . '/' . $type;
		}

		return Route::format($pattern, $target);
	}

	/**
	 * Return the URL type 'view' for the node.
	 *
	 * @param Node $node
	 */
	static public function get_url(ActiveRecord $node)
	{
		return $node->url('view');
	}

	/**
	 * Return the absolute URL type for the node.
	 *
	 * @param Node $node
	 * @param string $type The URL type.
	 */
	static public function absolute_url(ActiveRecord $node, $type='view')
	{
		global $core;

		try
		{
			$site = $node->site ? $node->site : $core->site;
		}
		catch (PropertyNotDefined $e)
		{
			$site = $core->site;
		}

		return $site->url . substr($node->url($type), strlen($site->path));
	}

	/**
	 * Return the _primary_ absolute URL for the node.
	 *
	 * @param Node $node
	 *
	 * @return string The primary absolute URL for the node.
	 */
	static public function get_absolute_url(Node $node)
	{
		return $node->absolute_url('view');
	}

	static private $view_target_cache = array();

	/**
	 * Returns the target page of a view.
	 *
	 * @param Site $site
	 * @param string $viewid Identifier of the view.
	 *
	 * @return \Icybee\Modules\Pages\Page
	 */
	static public function resolve_view_target(Site $site, $viewid)
	{
		global $core;

		if (isset(self::$view_target_cache[$viewid]))
		{
			return self::$view_target_cache[$viewid];
		}

		$targetid = $site->metas['views.targets.' . strtr($viewid, '.', '_')];

		return self::$view_target_cache[$viewid] = $targetid ? $core->models['pages'][$targetid] : false;
	}

	static private $view_url_cache = array();

	/**
	 * Returns the URL of a view.
	 *
	 * @param Site $site
	 * @param string $viewid
	 *
	 * @return string
	 */
	static public function resolve_view_url(Site $site, $viewid)
	{
		if (isset(self::$view_url_cache[$viewid]))
		{
			return self::$view_url_cache[$viewid];
		}

		$target = $site->resolve_view_target($viewid);

		return self::$view_url_cache[$viewid] = $target ? $target->url : '#unknown-target-for-view-' . $viewid;
	}

	/*
	 * MARKUPS
	 */

	/**
	 * Renders the specified view.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return mixed
	 */
	static public function markup_call_view(array $args, $engine, $template)
	{
		global $core;

		return $core->editors['view']->render($args['name'], $engine, $template);
	}
}