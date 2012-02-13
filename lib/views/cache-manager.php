<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Views;

class CacheManager implements \ICanBoogie\Modules\System\Cache\CacheInterface
{
	public $title = "Vues";
	public $description = "Index des vues des modules.";
	public $group = 'system';
	public $state = false;
	public $config_preview;

	public function __construct()
	{
		global $core;

		$this->state = $core->config['cache views'];
	}

	/**
	 * Clears the cache.
	 */
	public function clear()
	{
		global $core;

		unset($core->vars['cached_views']);

		return true;
	}

	/**
	 * Disables the cache.
	 *
	 * Unsets the `enable_modules_cache` var.
	 */
	public function disable()
	{
		global $core;

		unset($core->vars['enable_views_cache']);

		return true;
	}

	/**
	 * Enables the cache.
	 *
	 * Sets the `enable_modules_cache` var.
	 */
	public function enable()
	{
		global $core;

		$core->vars['enable_views_cache'] = true;

		return true;
	}

	/**
	 * Return stats about the cache.
	 */
	public function stat()
	{
		return \ICanBoogie\Modules\System\Cache\Module::get_vars_stat('#^cached_views$#');
	}
}