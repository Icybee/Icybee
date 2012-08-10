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
const VERSION = '1.0-dev (2012-08-10)';

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
 * might cause a hit on performance.
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

/**
 * The core instance is the heart of the ICanBoogie framework.
 *
 * @var Icybee\Core
 */
$core = new Core
(
	array
	(
		'paths' => array
		(
			'config' => $config,
			'locale' => $locale
		)
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
 * Request hooks
 */

namespace ICanBoogie;

use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;

Events::attach
(
	'ICanBoogie\HTTP\Dispatcher::populate', function(HTTP\Dispatcher\PopulateEvent $event, HTTP\Dispatcher $dispatcher)
	{
		/**
		 * Router for admin routes.
		 *
		 * This event hook handles all "/admin/" routes. It may redirect the user to the proper "admin"
		 * location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the Icybee admin
		 * interface is presented, granted the user has an access permission, otherwise the
		 * user is asked to authenticate.
		 */
		$event->controllers['admin_router'] = function(Request $request)
		{
			global $core;

			$path = Route::decontextualize($request->path);
			$path = rtrim($path, '/') . '/';

			if (strpos($path, '/admin/') !== 0)
			{
				return;
			}

			#
			# The site object is a dummy when there is no site defined (which should be an
			# exceptable error) or all defined sites use a path when the request doesn't. In that
			# case we need to redirect the user to the first site available.
			#

			$response = new Response();

			if (!$core->site_id)
			{
				try
				{
					$site = $core->models['sites']->order('weight')->one;

					if ($site)
					{
						$request_url = rtrim($core->site->url . $request->path, '/') . '/';
						$location = $site->url . $path;

						#
						# we don't redirect if the redirect location is the same as the request URL.
						#

						if ($request_url != $location)
						{
							$response->location = $site->url . $path;

							return $response;
						}
					}
				}
				catch (\Exception $e) { }

				log_error('You are on a dummy website. You should check which websites are available or create one if none are.');
			}

			$response->body = require \Icybee\ROOT . 'admin.php';

			return $response;
		};

		/**
 		 * Website router
 		 */
		$event->controllers['website_router'] = function(Request $request)
		{
			# core is reqquired by includes

			global $core;

			require_once DOCUMENT_ROOT . 'user-startup.php';

			$response = new Response();

			$pagemaker = new \Icybee\Pagemaker;
			$response->body = $pagemaker->run($request, $response);

			return $response;

			/*
			if ($core->user->is_guest && $event->request->method == Request::METHOD_GET)
			{
				$event->response->headers['Cache-Control'] = 'max-age=600';
			}
			else
			{
				$event->response->headers['Cache-Control'] = 'no-cache';
			}
			*/
		};
	}
);