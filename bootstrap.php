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

/**
 * Version string for the Icybee package.
 *
 * @var string
 */
const VERSION = 'dev-master (2012-12-06)';

/**
 * Root path for the Icybee package.
 *
 * @var string
 */
define('Icybee\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * Assets path for the Icybee package.
 *
 * @var string
 */
define('Icybee\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

$config = array();
$locale = array();

$vendor = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
$packages = array
(
	'icanboogie/common' => 'ICanBoogie\Common',
	'icanboogie/prototype' => 'ICanBoogie\Prototype',
	'icanboogie/activerecord' => 'ICanBoogie\ActiveRecord',
	'icanboogie/event' => 'ICanBoogie\Event',
	'icanboogie/http' => 'ICanBoogie\HTTP',
	'icanboogie/i18n' => 'ICanBoogie\I18n',
	'icanboogie/icanboogie' => 'ICanBoogie',
	'brickrouge/brickrouge' => 'Brickrouge',
	'icybee/bluetihi' => 'BlueTihi',
	'icybee/patron' => 'Patron'
);

/*
 * Icybee requires the ICanBoogie framework, the Brickrouge tookit and the Patron engine.
 *
 * If Phar packages are available they are used instead. You should pay attention to this as this
 * may cause a hit on performance.
 */
foreach ($packages as $package => $namespace)
{
	$pathname = $vendor . $package;
	$package_root = $pathname;

	if (file_exists($pathname . '.phar'))
	{
		$package_root = 'phar://' . $pathname . '.phar';

		require_once $pathname . '.phar';
	}

	$config[] = $package_root;
	$locale[] = $package_root;
}

$config[] = ROOT;
$locale[] = ROOT;

if (!class_exists('Icybee\Core', false))
{
	require_once ROOT . 'lib/core/core.php';
}


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

require_once ROOT . 'lib/helpers.php';
require_once ROOT . 'lib/helpers-compat.php';

/**
 * The core instance is the heart of the ICanBoogie framework.
 *
 * @var Icybee\Core
 */
$core = new Core
(
	array
	(
		'config paths' => $config,
		'locale paths' => $locale
	)
);

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

Event\attach(function(Dispatcher\CollectEvent $event, Dispatcher $dispatcher) {

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

		$path = normalize_url_path(Route::decontextualize($request->path));

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

				$route = $routes[$route_id]; //TODO-20120829: $route should be an object.

				return new RedirectResponse
				(
					Route::contextualize($route->pattern), 302, array
					(
						'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
					)
				);
			}
		}
	};
});

/**
 * Prevent members to access Icybee admin.
 */
Event\attach(function(Dispatcher\BeforeDispatchEvent $event, Dispatcher $target) { // TODO-20121219: move this to "members" module.

	global $core;

	$request = $event->request;

	if ($request->method != Request::METHOD_GET)
	{
		return;
	}

	$path = normalize_url_path(Route::decontextualize($request->path));

	if (strpos($path, '/admin/') !== 0)
	{
		return;
	}

	if (!($core->user instanceof \Icybee\Modules\Members\Member))
	{
		return;
	}

	throw new PermissionRequired("Members are not allowed to access the admin.");

});

Event\attach(function(Dispatcher\DispatchEvent $event, Dispatcher $target) {

	#
	# We chain the event so that it is called after the event callbacks have been processed,
	# for instance a _cache_ callback that may cache the response.
	#

	$event->chain(function(Dispatcher\DispatchEvent $event, Dispatcher $target) {

		$response = $event->response;

		if (!$response)
		{
			return;
		}

		if ($response->content_type->type != 'text/html')
		{
			return;
		}

		$response->body = (string) new \Icybee\StatsDecorator($response->body);
	});
});