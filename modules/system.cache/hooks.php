<?php

namespace ICanBoogie\Hooks\System;

use ICanBoogie\Event;
use ICanBoogie\Operation;

class Cache
{
	public static function clear_modules_cache()
	{
		$operation = Operation::decode('/api/system.cache/core.modules/clear');
		$operation();

		$operation = Operation::decode('/api/system.cache/core.configs/clear');
		$operation();

		$operation = Operation::decode('/api/system.cache/core.catalogs/clear');
		$operation();
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