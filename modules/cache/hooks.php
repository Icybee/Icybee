<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

use ICanBoogie\Event;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

class Hooks
{
	/**
	 * Clears the `core.modules`, `core.configs` and `core.catalogs` caches.
	 */
	static public function clear_modules_cache()
	{
		Request::from(Operation::encode('cache/core.modules/clear'))->post();
		Request::from(Operation::encode('cache/core.configs/clear'))->post();
		Request::from(Operation::encode('cache/core.catalogs/clear'))->post();
	}

	static public function on_modules_change(Event $event)
	{
		self::clear_modules_cache();
	}
}