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

use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;
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

		if (!($core->user instanceof \Icybee\Modules\Members\Member))
		{
			return;
		}

		throw new PermissionRequired("Members are not allowed to access the admin interface.");
	}

	/**
	 * Automatically logs the member after it has created its account.
	 *
	 * @param \ICanBoogie\Operation\ProcessEvent $event
	 * @param SaveOperation $target
	 */
	static public function on_save(\ICanBoogie\Operation\ProcessEvent $event, SaveOperation $target)
	{
		global $core;

		if ($target->key || !$core->user->is_guest)
		{
			return;
		}

		try
		{
			Request::from(Operation::encode('users/login'), array($_SERVER))->post
			(
				array
				(
					Member::USERNAME => $event->request['email'],
					Member::PASSWORD => $event->request['password']
				)
			);

			$event->response->location = $event->request['redirect_to'] ?: $target->record->url('profile');
		}
		catch (\Exception $e)
		{
			if (Debug::is_dev())
			{
				throw $e;
			}
			else
			{
				Debug::report($e);

				$target->record->login();
			}
		}
	}
}