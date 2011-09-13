<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

class List extends Node implements Iterator
{
	public $scope;
	public $description;

	protected function __get_nodes()
	{
		global $core;

		$model = $core->models['organize.lists/nodes'];

		$nodes = $model
		->select('lnode.*, node.constructor')
		->joins('INNER JOIN {prefix}nodes node ON lnode.nodeid = node.nid')
		->where('listid = ? AND node.is_online = 1 AND lnode.nodeid = node.nid', $this->nid)
		->order('weight')
		->all(\PDO::FETCH_CLASS, 'ICanBoogie\ActiveRecord\Organize\Lists\Node', array($model));

		$ids_by_constructor = array();
		$nodes_by_id = array();

		foreach ($nodes as $node)
		{
			$nid = $node->nodeid;

			$nodes_by_id[$nid] = $node;
			$ids_by_constructor[$node->constructor][] = $nid;
		}

		foreach ($ids_by_constructor as $constructor => $keys)
		{
			$model = $core->models[$constructor];

			$constructor_nodes = $model->find($keys);

			foreach ($constructor_nodes as $node)
			{
				$nid = $node->nid;

				if (!$node->is_online)
				{
					unset($nodes_by_id[$nid]);

					continue;
				}

				$nodes_by_id[$nid]->node = $node;
			}
		}

		return $nodes;
	}

	/*
	 * iterator
	 */

	private $position = 0;

    function rewind()
    {
    	$this->position = 0;
    }

    function current()
    {
    	return $this->nodes[$this->position];
    }

    function key()
    {
    	return $this->position;
    }

    function next()
    {
    	++$this->position;
    }

    function valid()
    {
    	return isset($this->nodes[$this->position]);
    }
}