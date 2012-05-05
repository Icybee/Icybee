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
 * Returns the usage (memory, files) of a specified cache.
 */
class StatOperation extends BaseOperation
{
	/**
	 * The method is defered to the "usage_<cache_id>" method.
	 *
	 * Using the mixin features of the Object class, one can add callbacks to get the usage of
	 * its cache.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		$collection = new Collection();
		$cache = $collection[$this->key];

		list($count, $label) = $cache->stat();

		$this->response['count'] = (int) $count;

		return $label;
	}

	protected function stat_core_assets()
	{
		global $core;

		$path = $core->config['repository.files'] . '/assets';

		return $this->get_files_stat($path);
	}
}