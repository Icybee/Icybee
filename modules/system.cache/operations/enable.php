<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

/**
 * Enables a cache.
 *
 * Before the cache is enabled it is first cleared.
 */
class EnableOperation extends BaseOperation
{
	protected function process()
	{
		$collection = new Collection();
		$cache = $collection[$this->key];

		$cache->clear();

		$this->response->success = array('The cache %cache has been enabled.', array('cache' => $this->key));

		return $cache->enable();
	}
}