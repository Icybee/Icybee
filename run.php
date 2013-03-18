<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

global $core;

Core::add_path(ROOT);

/**
 * The core instance is the heart of the ICanBoogie framework.
 *
 * @var Core
 */
$core = new Core
(
	array
	(
		'modules paths' => array
		(
			ROOT . 'modules' . DIRECTORY_SEPARATOR,
			dirname(__DIR__) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
		)
	)
);

\Brickrouge\Helpers::patch('t', 'ICanBoogie\I18n\t');
\Brickrouge\Helpers::patch('render_exception', 'ICanBoogie\Debug::format_alert');

\Brickrouge\Helpers::patch('get_document', function()
{
	return \ICanBoogie\Core::get()->document;
});

\Brickrouge\Helpers::patch('check_session', function()
{
	return \ICanBoogie\Core::get()->session;
});

/*
\Brickrouge\Helpers::patch('store_form_errors', function($name, $errors) {

	\ICanBoogie\Core::get()->session->brickrouge_errors[$name] = $errors;

});

\Brickrouge\Helpers::patch('retrieve_form_errors', function($name) {

	return \ICanBoogie\Core::get()->session->brickrouge_errors[$name];

});
*/

# FIXME-20121205: we need to run the core before attaching events, otherwise config events won't
# be attached.

// \ICanBoogie\log_time('core created');

$core->run();

// \ICanBoogie\log_time('core is running');

/*
 * HTTP dispatcher hook.
 *
 * Remember that our event callback is called *before* those defined by the modules.
 */
namespace ICanBoogie;

use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\HTTP\Request;

Event\attach(function(Dispatcher\CollectEvent $event, Dispatcher $dispatcher)
{
	/**
	 * Router for admin routes.
	 *
	 * This event hook handles all "/admin/" routes. It may redirect the user to the proper "admin"
	 * location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the Icybee admin
	 * interface is presented, granted the user has an access permission, otherwise the
	 * user is asked to authenticate.
	 */
	$event->dispatchers['admin:categories'] = function(Request $request)
	{
		global $core;

		$path = normalize_url_path(\ICanBoogie\Routing\decontextualize($request->path));

		if (strpos($path, '/admin/') !== 0)
		{
			return;
		}

		$category = substr($path, 7, -1);

		if ($category)
		{
			$user = $core->user;
			$routes = $core->routes;

			foreach ($core->modules->descriptors as $module_id => $descriptor)
			{
				if (!isset($core->modules[$module_id]) || !$user->has_permission(Module::PERMISSION_ACCESS, $module_id)
				|| $descriptor[Module::T_CATEGORY] != $category)
				{
					continue;
				}

				$route_id = "admin:$module_id";

				if (empty($routes[$route_id]))
				{
					$route_id = "admin:$module_id/manage"; //TODO-20120829: COMPAT, 'manage' should disappear.

					if (empty($routes[$route_id]))
					{
						continue;
					}
				}

				$route = $routes[$route_id];

				return new RedirectResponse
				(
					\ICanBoogie\Routing\contextualize($route->pattern), 302, array
					(
						'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
					)
				);
			}
		}
	};
});

Event\attach(function(Dispatcher\DispatchEvent $event, Dispatcher $target)
{
	#
	# We chain the event so that it is called after the event callbacks have been processed,
	# for instance a _cache_ callback that may cache the response.
	#

	$event->chain(function(Dispatcher\DispatchEvent $event, Dispatcher $target)
	{
		$response = $event->response;

		if (!$response || $response->content_type->type != 'text/html')
		{
			return;
		}

		$response->body = (string) new \Icybee\StatsDecorator($response->body);
	});
});