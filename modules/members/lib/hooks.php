<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Members;

use ICanBoogie\PermissionRequired;

class Hooks
{
	/**
	 * Prevents members from accessing the admin interface.
	 *
	 * @param \ICanBoogie\Routing\Dispatcher\BeforeDispatchEvent $event
	 * @param \ICanBoogie\Routing\Dispatcher $target
	 *
	 * @throws PermissionRequired if a member tries to access the admin interface.
	 */
	static public function before_routing_dispatcher_dispatch(\ICanBoogie\Routing\Dispatcher\BeforeDispatchEvent $event, \ICanBoogie\Routing\Dispatcher $target)
	{
		global $core;

		$request = $event->request;

		if ($request->decontextualized_path !== '/admin')
		{
			return;
		}

		if (!($core->user instanceof Member))
		{
			return;
		}

		throw new PermissionRequired("Members are not allowed to access the admin interface.");
	}
}