<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents;

/**
 * Manages cache for contents body.
 *
 * The state of the cache is saved in the registry under `contents.cache_rendered_body`.
 */
class CacheManager implements \ICanBoogie\Modules\System\Cache\CacheInterface
{
	public $title = "Corps des contenus";
	public $description = "Le rendu HTML du corps des contenus est mis en cache lorsqu'il diffère de la source.";
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

		if (!$count)
		{
			return array($count, 'Le cache est vide');
		}

		return array($count, $count . ' enregistrements<br /><span class="small">' . wd_format_size($size) . '</span>');
	}

	function clear()
	{
		global $core;

		return $core->models['contents/cache']->truncate();
	}
}