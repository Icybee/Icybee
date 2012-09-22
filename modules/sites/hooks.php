<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites;

use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Response;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Route;

class Hooks
{
	/**
	 * Redirects the request if it matches no site.
	 *
	 * Only online websites are used if the users is a guest or a member.
	 *
	 * @param Dispatcher\BeforeDispatchEvent $event
	 * @param Dispatcher $target
	 */
	static public function before_http_dispatcher_dispatch(Dispatcher\BeforeDispatchEvent $event, Dispatcher $target)
	{
		global $core;

		if ($core->site_id)
		{
			return;
		}

		$request = $event->request;

		if (!in_array($request->method, array(Request::METHOD_ANY, Request::METHOD_GET, Request::METHOD_HEAD)))
		{
			return;
		}

		$path = \ICanBoogie\normalize_url_path(Route::decontextualize($request->path));

		if (strpos($path, '/api/') === 0)
		{
			return;
		}

		try
		{
			$query = $core->models['sites']->order('weight');

			if ($core->user->is_guest || $core->user instanceof \ICanBoogie\ActiveRecord\Users\Member)
			{
				$query->where('status = 1');
			}

			$site = $query->one;

			if ($site)
			{
				$request_url = \ICanBoogie\normalize_url_path($core->site->url . $request->path);
				$location = \ICanBoogie\normalize_url_path($site->url . $path);

				#
				# we don't redirect if the redirect location is the same as the request URL.
				#

				if ($request_url != $location)
				{
					$query_string = $request->query_string;

					if ($query_string)
					{
						$location .= '?' . $query_string;
					}

					$event->response = new Response
					(
						302, array
						(
							'Location' => $location,
							'Icybee-Redirected-By' => __CLASS__ . '::' . __FUNCTION__
						)
					);

					return;
				}
			}
		}
		catch (\Exception $e) { }

		\ICanBoogie\log_error('You are on a dummy website. You should check which websites are available or create one if none are.');
	}

	/**
	 * Returns the site active record associated to the node.
	 *
	 * This is the getter for the nodes' `site` magic property.
	 *
	 * @param \ICanBoogie\ActiveRecord\Node $node
	 *
	 * @return \Icybee\Modules\Sites\Site|null The site active record associate with the node,
	 * or null if the node is not associated to a specific site.
	 */
	static public function get_node_site(\ICanBoogie\ActiveRecord\Node $node)
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
	 * This is the getter for the core's {@link \Icybee\Modules\Sites\Site::site} magic property.
	 *
	 * @return \Icybee\Modules\Sites\Site
	 */
	static public function get_core_site(\ICanBoogie\Core $core)
	{
		return Model::find_by_request($core->request);
	}

	/**
	 * Returns the key of the current site.
	 *
	 * This is the getter for the core's {@link \Icybee\Modules\Sites\Site::site_id} magic
	 * property.
	 *
	 * @param \ICanBoogie\Core $core
	 *
	 * @return int
	 */
	static public function get_core_site_id(\ICanBoogie\Core $core)
	{
		$site = self::get_core_site($core);

		return $site ? $site->siteid : null;
	}

	/**
	 * Returns the site active record for a request.
	 *
	 * This is the getter for the {@link \ICanBoogie\HTTP\Request\Context::site} magic property.
	 *
	 * @return \Icybee\Modules\Sites\Site
	 */
	static public function get_site_for_request_context(Request\Context $context)
	{
		return Model::find_by_request($context->request);
	}

	/**
	 * Returns the identifier of the site for a request.
	 *
	 * This is the getter for the {@link \ICanBoogie\HTTP\Request\Context::site_id} magic property.
	 *
	 * @return int
	 */
	static public function get_site_id_for_request_context(Request\Context $context)
	{
		return $context->site ? $context->site->siteid : null;
	}
}