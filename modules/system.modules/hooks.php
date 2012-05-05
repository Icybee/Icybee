<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Modules;

use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

class Hooks
{
	/**
	 * The cache is destroyed when modules are activated.
	 */
	public static function on_modules_activate()
	{
		Request::from(Operation::encode('system.cache/core.modules/clear'))->post();
		Request::from(Operation::encode('system.cache/core.configs/clear'))->post();
		Request::from(Operation::encode('system.cache/core.catalogs/clear'))->post();
	}

	/**
	 * The cache is destroyed when modules are deactivated.
	 */
	public static function on_modules_deactivate()
	{
		Request::from(Operation::encode('system.cache/core.modules/clear'))->post();
		Request::from(Operation::encode('system.cache/core.configs/clear'))->post();
		Request::from(Operation::encode('system.cache/core.catalogs/clear'))->post();
	}
}