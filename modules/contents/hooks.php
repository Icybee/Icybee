<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents;

use ICanBoogie;
use ICanBoogie\Event;
use ICanBoogie\Operation;

class Hooks
{
	public static function on_alter_cache_collection(Event $event, \ICanBoogie\Modules\System\Cache\Collection $collection)
	{
		$event->collection['contents.body'] = new CacheManager;
	}
}