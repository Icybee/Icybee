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
 * Configurations cache manager.
 */
class ConfigsCacheManager extends CacheManager
{
	public $title = "Configurations";
	public $description = "Configurations des différents composants du framework.";
	public $group = 'system';

	public function __construct()
	{
		global $core;

		$this->state = $core->config['cache configs'];
	}

	/**
	 * Clears the cache.
	 */
	public function clear()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';
		$files = glob(\ICanBoogie\DOCUMENT_ROOT . $path . '/config_*');

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}

	/**
	 * Disables the cache.
	 *
	 * Unsets the `enable_modules_cache` var.
	 */
	public function disable()
	{
		global $core;

		unset($core->vars['enable_configs_cache']);

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

		$core->vars['enable_configs_cache'] = true;

		return true;
	}

	/**
	 * Return stats about the cache.
	 */
	public function stat()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/core';

		return Module::get_files_stat($path, '#^config_#');
	}
}