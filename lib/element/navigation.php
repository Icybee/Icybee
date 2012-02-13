<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Admin\Element;

use ICanBoogie\ActiveRecord\Users;
use ICanBoogie\Module;
use ICanBoogie\Route;

/**
 * Admin navigation bar.
 */
class Navigation extends \Brickrouge\Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'div', $attributes + array
			(

			)
		);
	}

	protected function render_inner_html()
	{
		global $core;

		$rc = parent::render_inner_html();

		$links = array();
		$routes = Route::routes();
		$user = $core->user;

		foreach ($routes as $route)
		{
			if (empty($route['index']) || empty($route['workspace']))
			{
				continue;
			}

			$module_id = $route['module'];

			if (!isset($core->modules[$module_id]))
			{
				continue;
			}

			$permission = isset($route['permission']) ? $route['permission'] : Module::PERMISSION_ACCESS;

			if (!$user->has_permission($permission, $module_id))
			{
				continue;
			}

			$ws = $route['workspace'];

			$links[$ws] = t($ws, array(), array('scope' => 'module_category.title'));
		}

		uasort($links, 'wd_unaccent_compare_ci');

		$links = array_merge
		(
			array
			(
				'dashboard' => 'Dashboard',
				'features' => 'Features'
			),

			$links
		);

		$matching_route = Route::find($_SERVER['REQUEST_URI'], 'any', 'admin'); // FIXME-20120201: use the primary request object
		$selected = $matching_route ? $matching_route[0]['workspace'] : 'dashboard';

		$rc .= '<ul>';

		foreach ($links as $path => $label)
		{
			if (strpos($selected, $path) === 0)
			{
				$rc .= '<li class="selected">';

// 				$this->page_title = $label; FIXME-20120201: we use to set the page title here
			}
			else
			{
				$rc .= '<li>';
			}

			$path = Route::contextualize('/admin/'. $path);

			$rc .= '<a href="' . \ICanBoogie\escape($path) . '">' . $label . '</a>';
			$rc .= '</li>';
		}

		$rc .= '</ul>';

		return $rc;
	}
}