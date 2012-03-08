<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord;
use ICanBoogie\Exception;
use ICanBoogie\I18n\Tanslator\Proxi;
use ICanBoogie\Module;
use ICanBoogie\Route;

$user = $core->user;
$request = $core->request;

if ($user->is_guest)
{
	$request_route = '/admin/authenticate';
}
else
{
	if ($user->timezone)
	{
		$core->timezone = $user->timezone;
	}

	if ($user->language)
	{
		$core->language = $user->language;
	}

	$request_route = $request->pathinfo;

	if ($user instanceof ActionRecord\Users\Member)
	{
		$request_route = $site->path . '/admin/authenticate';

		wd_log_error('Members are not allowed to access the admin.');
	}

	$site = $core->site;

	if ($site->path && preg_match('#^' . preg_quote($site->path) . '/#', $request_route))
	{
		$request_route = substr($request_route, strlen($site->path));
	}

	#

	$restricted_sites = null;

	try
	{
		$restricted_sites = $user->restricted_sites_ids;
	}
	catch (Exception\PropertyNotFound $e)
	{
		throw $e;
	}
	catch (\Exception $e) { }

	if ($restricted_sites && !in_array($core->site_id, $restricted_sites))
	{
		$request_route = '/admin/available-sites';
	}
	else
	{
		$path = $core->site->path;

		if ($path && preg_match('#^' . preg_quote($path) . '/admin/?#', $request_route))
		{
			$request_route = substr($request_route, strlen($path));
		}

		if ($request_route == '/admin')
		{
			$request_route = '/admin/';
		}
	}
}

$routes = $core->configs['admin_routes'];

Route::add($routes);

#
# create location for workspaces // TODO-20091118: that's quite bad, but enought for the time being
#

function _create_ws_locations($routes)
{
	global $core;

	$add = array();
	$user = $core->user;

	foreach ($routes as $id => $route)
	{
		if (empty($route['pattern']) || empty($route['workspace']) || empty($route['index']) || empty($route['module']))
		{
			continue;
		}

		$module_id = $route['module'];

		if (!$user->has_permission(Module::PERMISSION_ACCESS, $module_id) || !isset($core->modules[$module_id]))
		{
			continue;
		}


		$pattern = '/admin/' . $route['workspace'];
		$route_id = 'redirect:' . $pattern;

		if (isset($add[$route_id]) || isset($add[$route_id]))
		{
			continue;
		}

		$location = $route['pattern'];

		$add[$route_id] = array
		(
			'pattern' => $pattern,
			'location' => $location
		);
	}

	foreach ($add as $route_id => $definition)
	{
		Route::add($route_id, $definition);
	}
}

_create_ws_locations($routes);

/*
 * special routes are created from modules descriptors. For exemple, one can define the route 'edit' which
 * will be updated using a union with complete pattern (replaces key), module reference and workspace...
 *
 */

function _route_add_block($route, $params)
{
	global $core;

	try
	{
		$module_id = $route['module'];
		$module = $core->modules[$module_id];

		array_unshift($params, $route['block']);

		$block = call_user_func_array(array($module, 'getBlock'), $params);

		if (is_array($block))
		{
			$block = $block['element'];
		}
	}
	catch (\Exception $e)
	{
		$block = '<div class="alert-wrapper">' . ICanBoogie\Debug::format_alert($e) . '</div>';
	}

	//$document->addToBlock((string) $block, 'contents');

	$core->document->addToBlock(is_object($block) ? $block->__toString() : (string) $block, 'contents');
}

/*
 * We search for a route matching the request.
 *
 * The route is saved under the `route` property of the request, or null if no matching route
 * was found.
 */
$match = Route::find($request_route, 'any', 'admin');

$core->request->route = null;

if ($match)
{
	list($route, $capture, $pattern) = $match;

	$core->request->route = $route;

	if (isset($route['location']))
	{
		$location = $route['location'];

		header('Location: ' . $core->site->path . $location);

		exit;
	}
}

if ($request_route == '/admin/available-sites')
{
	require_once 'route.available-sites.php';

	\Icybee\_route_add_available_sites();
}
else if ($match)
{
	_route_add_block($route, is_array($capture) ? $capture : array());
}
else
{
	if ($core->user_id == 1)
	{
		var_dump(Route::routes());
	}

	throw new Exception('There is no matching route for %path.', array('path' => $request_route));

	wd_log_error('unable to find matching pattern for route %route', array('%route' => $request_route));

	if ($core->user_id == 1)
	{
		$rc = \ICanBoogie\dump(Route::routes());

		$document->addToBlock($rc, 'contents');
	}
	else
	{

	}
}