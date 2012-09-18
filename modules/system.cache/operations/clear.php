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

		$this->response->message = array('The cache %cache has been cleared.', array('cache' => $this->key));

		return $cache->stat();
	}
}