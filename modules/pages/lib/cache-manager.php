<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

/**
 * Manages cache for contents body.
 *
 * The state of the cache is saved in the registry under `contents.cache_rendered_body`.
 */
class CacheManager implements \ICanBoogie\Modules\System\Cache\CacheInterface
{
	public $title = "Pages";
	public $description = "Pages rendues par Icybee.";
	public $group = 'contents';
	public $state = false;
	public $config_preview;

	public function __construct()
	{
		global $core;

		$this->state = isset($core->vars['enable_pages_cache']);
	}

	/**
	 * Enables page caching.
	 */
	public function enable()
	{
		global $core;

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$path = $core->config['repository.cache'] . '/pages';

		if (!is_writable($root . $path))
		{
			\ICanBoogie\log_error("%path is missing or not writable", array('%path' => $path));

			return false;
		}

		return $core->vars['enable_pages_cache'] = true;
	}

	/**
	 * Disables page caching.
	 */
	public function disable()
	{
		global $core;

		return $core->vars['enable_pages_cache'] = false;
	}

	/**
	 * Returns usage of the page cache.
	 */
	public function stat()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/pages';

		return \ICanBoogie\Modules\System\Cache\Module::get_files_stat($path);
	}

	/**
	 * Clears the page cache.
	 */
	public function clear()
	{
		global $core;

		$path = $core->config['repository.cache'] . '/pages';

		return \ICanBoogie\Modules\System\Cache\Module::clear_files($path);
	}
}