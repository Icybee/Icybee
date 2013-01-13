<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

use ICanBoogie\I18n;

/**
 * Manages cache for contents body.
 *
 * The state of the cache is saved in the registry under `contents.cache_rendered_body`.
 */
class CacheManager implements \Icybee\Modules\Cache\CacheManagerInterface
{
	public $title = "Contents body";
	public $description = "The rendered body of contents is cached.";
	public $group = 'contents';
	public $state = false;
	public $config_preview;

	public function __construct()
	{
		global $core;

		$this->state = !empty($core->registry['contents.cache_rendered_body']);
	}

	public function enable()
	{
		global $core;

		return $core->registry['contents.cache_rendered_body'] = true;
	}

	public function disable()
	{
		global $core;

		return $core->registry['contents.cache_rendered_body'] = false;
	}

	public function stat()
	{
		global $core;

		$model = $core->models['contents/cache'];

		list($count, $size) = $model->select('COUNT(nid) count, SUM(LENGTH(body)) size')->one(\PDO::FETCH_NUM);

		return array((int) $count, I18n\t(':count records<br /><span class="small">:size</span>', array(':count' => (int) $count, 'size' => \ICanBoogie\I18n\format_size($size))));
	}

	function clear()
	{
		global $core;

		return $core->models['contents/cache']->truncate();
	}
}