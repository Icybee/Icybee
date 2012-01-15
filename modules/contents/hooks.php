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

use ICanBoogie;
use ICanBoogie\Event;
use ICanBoogie\Operation;

class Hooks
{
	static public function alter_block_manage(Event $event)
	{
		global $core;

		$event->caches['contents.body'] = array
		(
			'title' => 'Corps des contenus',
			'description' => "Rendu HTML du corps des contenus, lorsqu'il diffère de la source.",
			'group' => 'contents',
			'state' => !empty($core->registry['contents.cache_rendered_body']),
			'size_limit' => false,
			'time_limit' => array(7, 'Jours')
		);
	}

	static public function enable_cache(\ICanBoogie\Modules\System\Cache\EnableOperation $operation)
	{
		global $core;

		return $core->registry['contents.cache_rendered_body'] = true;
	}

	static public function disable_cache(\ICanBoogie\Modules\System\Cache\DisableOperation $operation)
	{
		global $core;

		return $core->registry['contents.cache_rendered_body'] = false;
	}

	static public function stat_cache(\ICanBoogie\Modules\System\Cache\StatOperation $operation)
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

	static public function clear_cache(\ICanBoogie\Modules\System\Cache\ClearOperation $operation)
	{
		global $core;

		return $core->models['contents/cache']->truncate();
	}
}