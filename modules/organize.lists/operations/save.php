<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Organize\Lists;

class Save extends \ICanBoogie\Operation\Nodes\Save
{
	protected function process()
	{
		$rc = parent::process();

		try
		{
			$listid = $rc['key'];
			$model = $this->module->model('nodes');
			$model->where('listid = ?', $listid)->delete();

			$request = $this->request;
			$nodes = $params['nodes'];

			if ($nodes)
			{
				$labels = $params['labels'];

				$weight = 0;

				foreach ($nodes as $i => $nodeid)
				{
					$model->insert
					(
						array
						(
							'listid' => $listid,
							'nodeid' => $nodeid,
							'weight' => $weight++,
							'label' => $labels[$i]
						)
					);
				}
			}
		}
		catch (\Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return $rc;
	}
}