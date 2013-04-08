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

use ICanBoogie\Event;

use Icybee\Modules\Cache\Collection as CacheCollection;
use Icybee\Modules\Files\File;

class Hooks
{
	/*
	 * Events
	 */

	/**
	 * Adds the `contents.body` cache manager to the cache collection.
	 *
	 * @param CacheCollection\CollectEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
	{
		$event->collection['contents.body'] = new CacheManager;
	}

	/**
	 * The callback is called when the `Icybee\Modules\Files\File::move` is triggered, allowing us
	 * to update contents to the changed path of resources.
	 *
	 * @param File\Event $event
	 * @param File $target
	 */
	static public function on_file_move(File\MoveEvent $event, File $target)
	{
		global $core;

		$core->models['contents']->execute
		(
			'UPDATE {self} SET `body` = REPLACE(`body`, ?, ?)', array($event->from, $event->to)
		);
	}
}