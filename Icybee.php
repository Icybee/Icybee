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
define('Icybee\VERSION', '0.8.0-dev (2011-08-03)');

/**
 * @var string Root path for the Icybee package.
 */
define('Icybee\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * @var string Assets path for the Icybee package.
 */
define('Icybee\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/*
 * Icybee requires the ICanBoogie framework, the BrickRouge framework and the Patron engine.
 *
 * If Phar versions of theses packages are available they are used instead. You should pay
 * attention to this as this might cause a hit on performance.
 */
if (file_exists(ROOT . 'framework/ICanBoogie.phar'))
{
	require_once 'phar://' . ROOT . 'framework/ICanBoogie.phar/ICanBoogie.php';
}
else
{
	require_once ROOT . 'framework/ICanBoogie/ICanBoogie.php';
}

if (file_exists(ROOT . 'framework/BrickRouge.phar'))
{
	require_once 'phar://' . ROOT . 'framework/BrickRouge.phar/BrickRouge.php';
}
else
{
	require_once ROOT . 'framework/BrickRouge/BrickRouge.php';
}

require_once ROOT . 'lib/core/core.php';
require_once ROOT . 'includes/common.php';

/**
 * @var Icybee\Core The core instance is the heart of the ICanBoogie framework.
 */
$core = Core::get_singleton();

// wd_log_time('core created');

$core->run();

// wd_log_time('core is running');

/*
 * The following code is a tiny router to handle "/admin/" routes. It may redirect the user to the
 * properter "admin" location e.g. '/admin/' => '/fr/admin/'. If the "admin" route is detected, the
 * Icybee admin interface is presented, granted the user has an access permission, otherwise the
 * user is asked to authenticate.
 */

$uri = $_SERVER['REQUEST_URI'];
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
		$site = \ICanBoogie\Hooks\Sites::find_by_request(array('REQUEST_PATH' => '/', 'HTTP_HOST' => $_SERVER['HTTP_HOST']));

		if ($site->path)
		{
			header('Location: ' . $site->path . $uri);

			exit;
		}
	}

	require ROOT . 'admin.php';
}