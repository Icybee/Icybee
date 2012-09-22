<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

/**
 * Returns the usage (memory, files) of the specified cache.
 */
class StatOperation extends BaseOperation
{
	protected function process()
	{
		$cache = $this->collection[$this->key];

		list($count, $label) = $cache->stat();

		$this->response['count'] = (int) $count;

		return $label;
	}
}