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
 * @var float This is the time reference used by the wd_log_time() function.
 */
$wddebug_time_reference = microtime(true);

/**
 * @var string Version string for the Icybee package.
 */
define('Icybee\VERSION', '1.0-dev (2012-01-17)');

/**
 * @var string Root path for the Icybee package.
 */
define('Icybee\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * @var string Assets path for the Icybee package.
 */
define('Icybee\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/*
 * Icybee requires the ICanBoogie framework, the Brickrouge framework and the Patron engine.
 *
 * If Phar versions of theses packages are available they are used instead. You should pay
 * attention to this as this might cause a hit on performance.
 */

$framework = array('ICanBoogie', 'Brickrouge', 'Patron');

foreach ($framework as $name)
{
	if (file_exists(ROOT . "framework/$name.phar"))
	{
		require_once ROOT . "framework/$name.phar";
	}
	else
	{
		require_once ROOT . "framework/$name/startup.php";
	}
}

if (!class_exists('Icybee\Core', false))
{
	require_once ROOT . 'lib/core/core.php';
}

require_once ROOT . 'includes/common.php';
require_once ROOT . 'lib/helpers.php';

/**
 * @var Icybee\Core The core instance is the heart of the ICanBoogie framework.
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

/**
 * @var bool The views are cached when the Icybee\CACHE_VIEWS is defined.
 */
if (!defined('Icybee\CACHE_VIEWS'))
{
	define('Icybee\CACHE_VIEWS', $core->config['cache views']);
}

// wd_log_time('core created');

$core->run();

// wd_log_time('core is running');

/*
 * The following code is a tiny router to handle "/admin/" routes. It may redirect the user to the
 * proper "admin" location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the
 * Icybee admin interface is presented, granted the user has an access permission, otherwise the
 * user is asked to authenticate.
 */

$uri = $core->request->uri;
$site = $core->site;
$suffix = $site->path;

if ($suffix && preg_match('#^' . preg_quote($suffix) . '/#', $uri))
{
	$uri = substr($uri, strlen($suffix));
}

if (preg_match('#^/admin/#', $uri) || preg_match('#^/admin$#', $uri))
{
	if (!$site->siteid)
	{
// 		throw new \Exception('No site id');
		/*
		$site = \ICanBoogie\Modules\Sites\Hooks::find_by_request(array('REQUEST_PATH' => '/', 'HTTP_HOST' => $_SERVER['HTTP_HOST']));

		if ($site->path)
		{
			header('Location: ' . $site->path . $uri);

			exit;
		}
		*/
	}

	require ROOT . 'admin.php';
}