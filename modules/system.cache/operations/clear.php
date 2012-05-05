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

class ClearOperation extends BaseOperation
{
	protected function process()
	{
		$collection = new Collection();
		$cache = $collection[$this->key];

		$cache->clear();

		return $cache->stat();
	}

	/*DIRTY
	protected function clear_core_assets()
	{
		global $core;

		$path = $core->config['repository.files'] . '/assets';

		$files = glob($_SERVER['DOCUMENT_ROOT'] . $path . '/*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}
	*/
}