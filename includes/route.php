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

	$request_route = $request->path;

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
		$restricted_sites = $user->sites_ids;
	}
	catch (\Exception $e) { /* */ }

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

	$ws = array();
	$user = $core->user;

	foreach ($routes as $pattern => $route)
	{
		if (empty($route['workspace']) || empty($route['index']) || empty($route['module']))
		{
			continue;
		}

		$module_id = $route['module'];

		if (!$user->has_permission(Module::PERMISSION_ACCESS, $module_id) || !isset($core->modules[$module_id]))
		{
			continue;
		}

		$ws_pattern = '/admin/' . $route['workspace'];

		if (isset($ws[$ws_pattern]))
		{
			$cmp_route = $routes[$ws[$ws_pattern]['location']];

			$cmp_title = $core->modules->descriptors[$cmp_route['module']][Module::T_TITLE];
			$title = $core->modules->descriptors[$module_id][Module::T_TITLE];

			//wd_log('compare \1 and \2 == \3', array($cmp_title, $title, strcmp($cmp_title, $title)));

			if (strcmp($cmp_title, $title) < 0)
			{
				continue;
			}
		}

		$ws[$ws_pattern]= array
		(
			'location' => $pattern
		);
	}

	$routes += $ws;

	//wd_log('ws: \1', array($ws));

	return $routes;
}

$routes = _create_ws_locations($routes);

/*
 * special routes are created from modules descriptors. For exemple, one can define the route 'edit' which
 * will be updated using a union with complete pattern (replaces key), module reference and workspace...
 *
 */

function _route_add_block($route, $params)
{
	global $core, $document;

	try
	{
		$module_id = $route['module'];
		$module = $core->modules[$module_id];

		array_unshift($params, $route['block']);

		//wd_log('params: \1', array($params));

		$block = call_user_func_array(array($module, 'getBlock'), $params);

		if (is_array($block))
		{
			$block = $block['element'];
		}
	}
	catch (\Exception $e)
	{
		$block = '<div class="group">' . $e . '</div>';
	}

	//$document->addToBlock((string) $block, 'contents');

	$document->addToBlock(is_object($block) ? $block->__toString() : (string) $block, 'contents');
}

function _route_add_options($requested, $req_pattern)
{
	global $core, $document, $routes;

	if (empty($requested['workspace']))
	{
		return;
	}

	$req_ws = $requested['workspace'];
	$req_module = $requested['module'];

	$options = array();
	$user = $core->user;

	foreach ($routes as $pattern => $route)
	{
		if (is_numeric($pattern))
		{
			continue;
		}

		$module = isset($route['module']) ? $route['module'] : null;

		if (!$module || $module != $req_module)
		{
			continue;
		}

		$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

		if (!$user->has_permission($permission, $module))
		{
			continue;
		}

		/*
		 * TODO: implement acces callback
		 *
		 */

		// TODO: les blocs qui utilisent des patterns devrait avoir une visibility = true

		/*
		if (empty($route['visibility']))
		{
			throw new Exception
			(
				'Missing %parameter for route %pattern !definition', array
				(
					'%parameter' => 'visibility',
					'%pattern' => $pattern,
					'!definition' => $route
				)
			);
		}
		*/

		if (empty($route['visibility']) || ($route['visibility'] == 'auto' && $pattern != $req_pattern))
		{
			continue;
		}

		$options[$pattern] = $route;
	}

	$items = null;

	global $request_route;

	$suffix = $core->site->path;

	foreach ($options as $pattern => $route)
	{
		if (empty($route['title']))
		{
			continue;
		}

		$title = $route['title'];

		if ($title{0} == '.')
		{
			$title = t(substr($title, 1), array(), array('scope' => array('block', 'title')));
		}
		else
		{
			$title = t($title);
		}

		$title = wd_entities($title);

		if ($req_pattern == $pattern)
		{
			$items .= '<li class="selected">';
			$items .= '<a href="' . $suffix . $request_route . '">' . $title . '</a>';
			$items .= '</li>';
		}
		else
		{
			$items .= '<li>';
			$items .= '<a href="' . $suffix . $pattern . '">' . $title . '</a>';
			$items .= '</li>';
		}
	}

	if ($items)
	{
		$items = '<ul class="items">' . $items . '</ul>';
	}

	$options = $document->getBlock('menu-options');

	$block = <<<EOT
		<div id="menu">
			$items
			<div id="menu-options">$options</div>
			<div class="clear"></div>
		</div>
EOT;

	$document->addToBlock($block, 'contents-header');
}

/*
 *
 */

function _route_add_tabs($requested, $req_pattern)
{
	global $core, $routes;

	$user = $core->user;
	$document = $core->document;
	$modules = $core->modules;

	if (!isset($requested['workspace']))
	{
		throw new Exception('Missing <em>workspace</em> for requested route !requested', array('!requested' => $requested));
	}

	$req_ws = $requested['workspace'];
	$req_module = $requested['module'];

	$tabs = array();

	foreach ($routes as $pattern => $route)
	{
		if (empty($route['workspace']) || $route['workspace'] != $req_ws)
		{
			//wd_log('discard pattern %pattern because ws %ws != %req', array('%pattern' => $pattern, '%ws' => isset($route['workspace']) ? $route['workspace'] : null, '%req' => $req_ws));

			continue;
		}

		if (empty($route['index']))
		{
			continue;
		}

		if (empty($modules[$route['module']]))
		{
			continue;
		}

		if (!$user->has_permission(Module::PERMISSION_ACCESS, $route['module']))
		{
			continue;
		}

		$tabs[$pattern] = $route;
	}

	if (!$tabs)
	{
		return;
	}

	//wd_log('tabs routes: \1', array($tabs));

	$descriptors = &$modules->descriptors;

	foreach ($tabs as $pattern => &$route)
	{
		$module_id = $route['module'];
		$module_flat_id = strtr($module_id, '.', '_');

		$default = $descriptors[$module_id][Module::T_TITLE];

		$route['tab-title'] = t($module_flat_id, array(), array('scope' => array('module', 'title'), 'default' => $default));
	}

	wd_array_sort_by($tabs, 'tab-title');

	$rc = '<ul class="tabs">';

	foreach ($tabs as $pattern => $route)
	{
		if ($route['module'] == $req_module)
		{
			$rc .= '<li class="selected">';

			$document->title = $route['tab-title'];
		}
		else
		{
			$rc .= '<li>';
		}


		$rc .= '<a href="' . $core->site->path . $pattern . '">' . t($route['tab-title']) . '</a></li>';
	}

	$rc .= '</ul>';
	$rc .= '<div class="clear"></div>';

	$document->addToBlock($rc, 'contents-header');
}

/*
 *
 */

$matching_route = null;

foreach ($routes as $pattern => $route)
{
	$match = Route::match($request_route, $pattern);

	if ($match === false)
	{
		continue;
	}

	if (isset($route['location']))
	{
		$location = $route['location'];

		header('Location: ' . $core->site->path . $location);

		exit;
	}

	$matching_route = $route;

	//wd_log('pattern: %pattern, match: !match, route: !route', array('%pattern' => $pattern, '!match' => $match, '!route' => $route));

	break;
}

if ($request_route == '/admin/available-sites')
{
	require_once 'route.available-sites.php';

	_route_add_available_sites();
}
else if ($matching_route)
{
	_route_add_block($route, is_array($match) ? $match : array());
	_route_add_tabs($route, $pattern);
	_route_add_options($route, $pattern);
}
else
{
	wd_log_error('unable to find matching pattern for route %route', array('%route' => $request_route));

	if ($core->user_id == 1)
	{
		$rc = wd_dump(array_keys($routes));

		$document->addToBlock($rc, 'contents');
	}
}