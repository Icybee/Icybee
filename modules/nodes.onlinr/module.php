<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes\Onlinr;

use ICanBoogie\Event;
use ICanBoogie\Operation;

use Brickrouge\Element;

class Module extends \Icybee\Module
{
	const REGISTRY_NEXTUPDATE = 'nodes_onlinr.next_update';

	public function run()
	{
		global $core;

		#
		# changes only happen at night, before the sun arise.
		#

		$hour = date('H');

		if ($hour > 6)
		{
			return;
		}

		try
		{
			$nextUpdate = $core->registry[self::REGISTRY_NEXTUPDATE];
			$nextUpdateTime = strtotime($nextUpdate);

			if (strtotime(date('Y-m-d')) <= $nextUpdateTime)
			{
				return;
			}

			$core->registry[self::REGISTRY_NEXTUPDATE] = $nextUpdateTime ? date('Y-m-d', strtotime('+1 day', $nextUpdateTime)) : date('Y-m-d');
		}
		catch (Exception $e) { return; }

		#
		#
		#

		$model = $this->model();
		$delete = array();

		$nodesModel = $core->models['nodes'];

		#
		# between publicize and privatize
		#

		$entries = $model->select('nid')->where('(publicize + 0 != 0 AND publicize <= CURRENT_DATE) AND (privatize + 0 != 0 AND privatize >= CURRENT_DATE)')->all(\PDO::FETCH_COLUMN);

		if ($entries)
		{
			$rc = $nodesModel->execute('UPDATE {self} SET is_online = 1 WHERE is_online = 0 AND nid IN(' . implode(',', $entries) . ')');
		}

		#
		# publicize only
		#

		$entries = $model->select('nid')->where('publicize <= CURRENT_DATE AND privatize + 0 = 0')->all(\PDO::FETCH_COLUMN);

		if ($entries)
		{
			$delete += array_flip($entries);

			$rc = $nodesModel->execute('UPDATE {self} SET is_online = 1 WHERE is_online = 0 AND nid IN(' . implode(',', $entries) . ')');
		}

		#
		# privatize
		#

		$entries = $model->select('nid')->where('privatize <= CURRENT_DATE')->all(\PDO::FETCH_COLUMN);

		if ($entries)
		{
			$delete += array_flip($entries);

			$rc = $nodesModel->execute('UPDATE {self} SET is_online = 0 WHERE is_online = 1 AND nid IN(' . implode(',', $entries) . ')');
		}

		if (0)
		{
			#
			# clean
			#

			$entries = $nodesModel->select('nid')->all(\PDO::FETCH_COLUMN);

			if ($entries)
			{
				$deprecated = $model->select('nid')->where(array('!nid' => $entries))->all(\PDO::FETCH_COLUMN);

				if ($deprecated)
				{
					$delete += array_flip($deprecated);
				}
			}
		}

		if ($delete)
		{
			$model->where(array('nid' => array_keys($delete)))->delete();
		}
	}

	public function event_operation_save(Event $event, \ICanBoogie\Modules\Nodes\SaveOperation $sender)
	{
		$params = &$sender->params;

		if (empty($params['nodes_onlinr']))
		{
			return;
		}

		$onlinr = $params['nodes_onlinr'];
		$nid = $event->rc['key'];

		if (!$onlinr['publicize'] && !$onlinr['privatize'])
		{
			$this->model->delete($nid);
		}
		else
		{
			$this->model->insert
			(
				array
				(
					'nid' => $nid,
					'publicize' => $onlinr['publicize'],
					'privatize' => $onlinr['privatize']
				),

				array
				(
					'on duplicate' => true
				)
			);
		}
	}
}