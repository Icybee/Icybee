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
const VERSION = '1.2 (2012-09-16)';

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

/*
 * Icybee requires the ICanBoogie framework, the Brickrouge tookit and the Patron engine.
 *
 * If Phar packages are available they are used instead. You should pay attention to this as this
 * may cause a hit on performance.
 */
foreach (array('ICanBoogie', 'Brickrouge', 'BlueTihi') as $name)
{
	$pathname = 'framework/' . $name;

	if (file_exists(ROOT . $pathname . '.phar'))
	{
		require_once ROOT . $pathname . '.phar';
	}
	else
	{
		require_once ROOT . $pathname . '/startup.php';
	}

	$package_root = constant($name . '\ROOT');

	$config[] = $package_root;
	$locale[] = $package_root;
}

$config[] = ROOT;
$locale[] = ROOT;

require_once ROOT . 'engines/Patron/startup.php';

$config[] = \Patron\ROOT;
$config[] = \Patron\ROOT;

if (!class_exists('Icybee\Core', false))
{
	require_once ROOT . 'lib/core/core.php';
}

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

// \ICanBoogie\log_time('core created');

$core->run();

// \ICanBoogie\log_time('core is running');

/**
 * The views are cached when the Icybee\CACHE_VIEWS is defined.
 *
 * @var bool
 */
defined('Icybee\CACHE_VIEWS') or define('Icybee\CACHE_VIEWS', $core->config['cache views']);

/*
 * HTTP dispatcher hook.
 *
 * Remember that our event callback is called *before* those defined by the modules.
 */
namespace ICanBoogie;

use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;

Events::attach
(
	'ICanBoogie\HTTP\Dispatcher::populate', function(Dispatcher\PopulateEvent $event, Dispatcher $dispatcher)
	{
		/**
		 * Router for admin routes.
		 *
		 * This event hook handles all "/admin/" routes. It may redirect the user to the proper "admin"
		 * location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the Icybee admin
		 * interface is presented, granted the user has an access permission, otherwise the
		 * user is asked to authenticate.
		 */
		$event->controllers['admin:categories'] = function(Request $request)
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

					return new Response
					(
						302, array
						(
							'Location' => Route::contextualize($route->pattern),
							'Icybee-Redirected-By' => __FILE__ . '::' . __LINE__
						)
					);
				}
			}
		};
	}
);

Events::attach
(
	'ICanBoogie\HTTP\Dispatcher::dispatch', function(Dispatcher\DispatchEvent $event, Dispatcher $target)
	{
		#
		# We chain the event so that it is called after the event callbacks have been processed,
		# for instance a _cache_ callback that may cache the response.
		#

		$event->chain(function(Dispatcher\DispatchEvent $event, Dispatcher $target) {

			$response = $event->response;

			if ($response->content_type->type != 'text/html')
			{
				return;
			}

			$response->body = (string) new \Icybee\StatsDecorator($response->body);
		});
	}
);