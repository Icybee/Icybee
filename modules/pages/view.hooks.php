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
use ICanBoogie\ActiveRecord\Site;
use ICanBoogie\Route;
use ICanBoogie\ActiveRecord\Node;

class site_pages_view_WdHooks
{
	private static $pages_model;
	private static $url_cache_by_siteid = array();

	public static function url(\ICanBoogie\ActiveRecord $target, $type='view')
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

		$constructor = isset($target->constructor) ? $target->constructor : $target->_model->id;
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
	static public function get_url(Node $node)
	{
		return $node->url('view');
	}

	/**
	 * Return the absolute URL type for the node.
	 *
	 * @param Node $node
	 * @param string $type The URL type.
	 */
	static public function absolute_url(Node $node, $type='view')
	{
		global $core;

		$site = $node->site ? $node->site : $core->site;

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

	static private $view_target_cache=array();

	/**
	 * Returns the page target of a view.
	 *
	 * @return ICanBoogie\ActiveRecord\Site The page target for the specified view identifier.
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

	static private $view_url_cache=array();

	/**
	 * Returns the URL of a view.
	 *
	 * @param ICanBoogie\ActiveRecord\Site $site
	 * @param string $viewid
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
}