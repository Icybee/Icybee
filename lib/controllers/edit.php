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

use ICanBoogie\HTTP\Request;

class EditController extends BlockController
{
	/*
	public function __invoke(Request $request)
	{
		global $core;

		$route = $this->route;
		$module = $core->modules[$route->module];

		if (!$core->user->has_permission(Module::PERMISSION_ACCESS, $module))
		{
			throw new \Exception('fcuk!');
		}

		$route = $this->route;

		$block = $module->getBlock($route->block, $request['key']);
		$decorator = (string) new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($block));

		return $decorator;
	}

	protected function get_component()
	{
		global $core;

		$route = $this->route;
		$request = $this->request;
		$module = $core->modules[$route->module];

		return $module->getBlock($route->block, $request['key']);
	}
	*/
}
