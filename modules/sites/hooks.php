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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Site;

class Sites
{
	static private $model;

	static public function find_by_request($request, $user=null)
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

		$path = $request['REQUEST_PATH'];

		if (preg_match('#/index\.(html|php)#', $path))
		{
			$path = '/';
		}

		$parts = array_reverse(explode('.', $request['HTTP_HOST']));

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

	static public function __get_site_id($target)
	{
		$site = self::__get_site($target);

		return $site ? $site->siteid : null;
	}

	static public function __get_site($target)
	{
		if ($target instanceof ActiveRecord\Node)
		{
			global $core;

			if (!$target->siteid)
			{
				return null;
			}

			return $core->site_id == $target->siteid ? $core->site : $core->models['sites'][$target->siteid];
		}

		return self::find_by_request($_SERVER);
	}

	static private function get_default_site()
	{
		global $core;

		$site = new Site();

		$site->siteid = 0;
		$site->title = 'Undefined';
		$site->admin_title = '';
		$site->subdomain = '';
		$site->domain = '';
		$site->tld = '';
		$site->path = '';
		$site->language = $core->language;
		$site->status = 1;

		return $site;
	}
}