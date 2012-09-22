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
 * Modules cache manager.
 */
class ModulesCacheManager extends CacheManager
{
	const REGEX = '/^cached_modules_/';

	public $title = "Modules";
	public $description = "Index des modules disponibles pour le framework.";
	public $group = 'system';

	public function __construct()
	{
		global $core;

		$this->state = $core->config['cache modules'];
	}

	/**
	 * Clears the cache.
	 */
	public function clear()
	{
		global $core;

		$iterator = $core->vars->matching(self::REGEX);
		$iterator->delete();

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

		unset($core->vars['enable_modules_cache']);

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

		$core->vars['enable_modules_cache'] = true;

		return true;
	}

	/**
	 * Return stats about the cache.
	 */
	public function stat()
	{
		return Module::get_vars_stat(self::REGEX);
	}
}