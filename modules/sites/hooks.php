<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Sites;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Site;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Request\Context;

class Hooks
{
	static private $model;

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
		return Model::find_by_request($core->request);
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

	/**
	 * Returns the site active record for a request.
	 *
	 * This is the getter for the {@link \ICanBoogie\HTTP\Request\Context::site} magic property.
	 *
	 * @return \ICanBoogie\ActiveRecord\Site
	 */
	public static function get_site_for_request_context(Context $context)
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
	public static function get_site_id_for_request_context(Context $context)
	{
		return $context->site ? $context->site->siteid : null;
	}
}