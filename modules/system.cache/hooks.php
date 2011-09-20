<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks\System;

use ICanBoogie\Event;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

class Cache
{
	public static function clear_modules_cache()
	{
		Request::from
		(
			array
			(
				'path' => '/api/system.cache/core.modules/clear'
			)
		)
		->post();

		Request::from
		(
			array
			(
				'path' => '/api/system.cache/core.configs/clear'
			)
		)
		->post();

		Request::from
		(
			array
			(
				'path' => '/api/system.cache/core.catalogs/clear'
			)
		)
		->post();
	}

	public static function on_modules_activate(Event $event)
	{
		self::clear_modules_cache();
	}

	public static function on_modules_deactivate(Event $event)
	{
		self::clear_modules_cache();
	}
}