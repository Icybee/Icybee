<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks;

use ICanBoogie\ActiveRecord,
	ICanBoogie\ActiveRecord\Site,
	ICanBoogie\HTTP\Request;

class Sites
{
	static private $model;

	static public function find_by_request(Request $request, $user=null)
	{
		global $core;

		$sites = $core->vars['sites'];

		if ($sites)
		{
			$sites = unserialize($sites);
		}

		if (!$sites)
		{
			try
			{
				$sites = $core->models['sites']->all;

				$core->vars['sites'] = serialize($sites);
			}
			catch (\Exception $e)
			{
				return self::get_default_site();
			}
		}

		$path = $request->path;

		/* FIXME-20111101: do we still need that now that we use the Request object ?
		if (preg_match('#/index\.(html|php)#', $path))
		{
			$path = '/';
		}
		*/

		$parts = array_reverse(explode('.', $request->headers['Host']));

		$tld = null;
		$domain = null;
		$subdomain = null;

		if (isset($parts[0]))
		{
			$tld = $parts[0];
		}

		if (isset($parts[1]))
		{
			$domain = $parts[1];
		}

		if (isset($parts[2]))
		{
			$subdomain = implode('.', array_slice($parts, 2));
		}

		$match = null;
		$match_score = -1;

		foreach ($sites as $site)
		{
			$score = 0;

			if ($site->status != 1 && $user && $user->is_guest)
			{
				continue;
			}

			if ($site->tld)
			{
				$score += ($site->tld == $tld) ? 1000 : -1000;
			}

			if ($site->domain)
			{
				$score += ($site->domain == $domain) ? 100 : -100;
			}

			if ($site->subdomain)
			{
				$score += ($site->subdomain == $subdomain || (!$site->subdomain && $subdomain == 'www')) ? 10 : -10;
			}

			$site_path = $site->path;

			if ($site_path)
			{
				$score += ($path == $site_path || preg_match('#^' . $site_path . '/#', $path)) ? 1 : -1;
			}
			else if ($path == '/')
			{
				$score += 1;
			}

			//echo "$site->title ($site->admin_title) scored: $score<br>";

			if ($score > $match_score)
			{
				$match = $site;
				$match_score = $score;
			}
		}

		if (!$match && $path == '/')
		{
			foreach ($sites as $site)
			{
				if ($site->status == 1)
				{
					return $site;
				}
			}
		}

		return $match ? $match : self::get_default_site();
	}

	/**
	 * Returns the site active record associated to the node.
	 *
	 * This is the getter for the nodes' `site` magic property.
	 *
	 * @param ActiveRecord\Node $node
	 *
	 * @return \ICanBoogie\ActiveRecord\Site|null The site active record associate with the node,
	 * or null if the node is not associated to a specific site.
	 */
	static public function __get_node_site(ActiveRecord\Node $node)
	{
		global $core;

		if (!$node->siteid)
		{
			return null;
		}

		return $core->site_id == $node->siteid ? $core->site : $core->models['sites'][$node->siteid];
	}

	/**
	 * Returns the active record for the current site.
	 *
	 * This is the getter for the core's {@link \ICanBoogie\ActiveRecord\Site::site} magic property.
	 *
	 * @return \ICanBoogie\ActiveRecord\Site
	 */
	static public function __get_core_site(\ICanBoogie\Core $core)
	{
		return self::find_by_request($core->request);
	}

	/**
	 * Returns the key of the current site.
	 *
	 * This is the getter for the core's {@link \ICanBoogie\ActiveRecord\Site::site_id} magic
	 * property.
	 *
	 * @param \ICanBoogie\Core $core
	 *
	 * @return int
	 */
	static public function __get_core_site_id(\ICanBoogie\Core $core)
	{
		$site = self::__get_core_site($core);

		return $site ? $site->siteid : null;
	}

	static private $default_site;

	/**
	 * Returns a default site active record.
	 *
	 * @return ActiveRecord\Site
	 */
	static private function get_default_site()
	{
		global $core;

		if (self::$default_site === null)
		{
			self::$default_site = Site::from
			(
				array
				(
					'title' => 'Undefined',
					'language' => $core->language,
					'timezone' => $core->timezone,
					'status' => 1
				)
			);
		}

		return self::$default_site;
	}
}