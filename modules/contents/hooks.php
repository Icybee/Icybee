<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

use ICanBoogie\Modules\System\Cache\Collection as CacheCollection;

class Hooks
{
	/**
	 * Adds the `contents.body` cache manager to the cache collection.
	 *
	 * @param CacheCollection\AlterEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_alter_cache_collection(CacheCollection\AlterEvent $event, CacheCollection $collection)
	{
		$event->collection['contents.body'] = new CacheManager;
	}
}