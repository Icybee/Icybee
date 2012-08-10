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
use ICanBoogie\Modules\System\Cache\Collection as CacheCollection;
use ICanBoogie\Operation;

class Hooks
{
	public static function on_alter_cache_collection(CacheCollection\AlterEvent $event, CacheCollection $collection)
	{
		$event->collection['contents.body'] = new CacheManager;
	}
}