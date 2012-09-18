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

class BlockController extends \ICanBoogie\Controller
{
	protected $request;

	public function __invoke(Request $request)
	{
		$this->request = $request;
		$this->control();

		$component = $this->get_component();

		return (string) $this->decorate($component);
	}

	protected function control()
	{
		if (!$this->control_permission(Module::PERMISSION_ACCESS))
		{
			throw new \ICanBoogie\PermissionRequired();
		}
	}

	protected function control_permission($permission)
	{
		global $core;

		$route = $this->route;
		$module = $core->modules[$route->module];

		return $core->user->has_permission(Module::PERMISSION_ACCESS, $module);
	}

	protected function get_component()
	{
		global $core;

		$route = $this->route;
		$module = $core->modules[$route->module];

		$args = array
		(
			$route->block
		);

		foreach ($this->request->path_params as $param => $value)
		{
			if (is_numeric($param))
			{
				$args[] = $value;
			}
		}

		return call_user_func_array(array($module, 'getBlock'), $args);
	}

	protected function decorate($component)
	{
		return new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($component));
	}
}
