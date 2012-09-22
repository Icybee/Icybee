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
 * Disables the specified cache.
 */
class DisableOperation extends BaseOperation
{
	protected function process()
	{
		$cache = $this->collection[$this->key];

		$this->response->message = array('The cache %cache has been disabled.', array('cache' => $this->key));

		return $cache->disable();
	}
}