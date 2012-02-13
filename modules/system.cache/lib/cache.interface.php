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

interface CacheInterface
{
	/**
	 * Clears the cache.
	 */
	public function clear();

	/**
	 * Disables the cache.
	 */
	public function disable();

	/**
	 * Enables the cache.
	 */
	public function enable();

	/**
	 * Return stats about the cache.
	 */
	public function stat();
}