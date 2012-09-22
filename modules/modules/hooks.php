<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Modules;

use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

class Hooks
{
	/**
	 * Destory system caches when modules are modified.
	 */
	public static function revoke_caches()
	{
		Request::from(Operation::encode('system.cache/core.modules/clear'))->post();
		Request::from(Operation::encode('system.cache/core.configs/clear'))->post();
		Request::from(Operation::encode('system.cache/core.catalogs/clear'))->post();
	}
}