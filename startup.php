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
 * This is the time reference used by the {@link wd_log_time()} function.
 *
 * @var float
 */
$wddebug_time_reference = microtime(true);

/**
 * Version string for the Icybee package.
 *
 * @var string
 */
define('Icybee\VERSION', '1.0-dev (2012-03-12)');

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

/*
 * Icybee requires the ICanBoogie framework, the Brickrouge tookit and the Patron engine.
 *
 * If Phar packages are available they are used instead. You should pay attention to this as this
 * might cause a hit on performance.
 */
foreach (array('ICanBoogie', 'Brickrouge', 'Patron') as $name)
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
}

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
$core = Core::get_singleton
(
	array
	(
		'paths' => array
		(
			'config' => array
			(
				\Brickrouge\ROOT,
				\Patron\ROOT,
				ROOT
			),

			'locale' => array
			(
				\Brickrouge\ROOT,
				\Patron\ROOT,
				ROOT
			)
		)
	)
);

// wd_log_time('core created');

$core->run();

// wd_log_time('core is running');

/**
 * The views are cached when the Icybee\CACHE_VIEWS is defined.
 *
 * @var bool
 */
if (!defined('Icybee\CACHE_VIEWS'))
{
	define('Icybee\CACHE_VIEWS', $core->config['cache views']);
}

/*
 * Request hooks
 */

use ICanBoogie\Events;
use ICanBoogie\HTTP\Request;

/**
 * Request hook for the website.
 */
Events::attach
(
	'ICanBoogie\HTTP\Request::dispatch', function(Request\DispatchEvent $event, Request $request)
	{
		global $core;

		require_once \ICanBoogie\DOCUMENT_ROOT . 'user-startup.php';

		$icybee = \Icybee::get_singleton();
		$event->response->body = $icybee->run($request, $event->response);
		$event->stop();

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
	}
);

/**
 * Request hook for the admin.
 *
 * This event hook handles all "/admin/" routes. It may redirect the user to the proper "admin"
 * location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the Icybee admin
 * interface is presented, granted the user has an access permission, otherwise the
 * user is asked to authenticate.
 */
Events::attach
(
	'ICanBoogie\HTTP\Request::dispatch', function(Request\DispatchEvent $event, request $request)
	{
		global $core;

		$path_info = $request->path_info;
		$site = $core->site;
		$suffix = $site->path;

		# decontextualize path_info

		if ($suffix && strpos($path_info, $suffix . '/') === 0)
		{
			$path_info = substr($path_info, strlen($suffix));
		}

		$path_info = rtrim($path_info, '/') . '/';

		if (strpos($path_info, '/admin/') === 0)
		{
// 			$event->response->headers['Cache-Control'] = 'private, no-cache';
			$event->response->body = require ROOT . 'admin.php';
			$event->stop();
		}
	}
);