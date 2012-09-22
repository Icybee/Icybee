<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Dashboard;

use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Route;

class Hooks
{
	/**
	 * If the user is authenticated and the request path is "/admin" the request is redirected to
	 * the dashboard.
	 *
	 * @param Dispatcher\BeforeDispatchEvent $event
	 * @param Dispatcher $dispatcher
	 */
	static public function before_dispatcher_dispatch(Dispatcher\BeforeDispatchEvent $event, Dispatcher $dispatcher)
	{
		global $core;

		$path = \ICanBoogie\normalize_url_path(Route::decontextualize($event->request->path));

		if ($path != '/admin/' || !$core->user_id || $core->user instanceof \Icybee\Modules\Members\Member)
		{
			return;
		}

		$event->response = new Response(302, array('Location' => Route::contextualize('/admin/dashboard')));
	}
}