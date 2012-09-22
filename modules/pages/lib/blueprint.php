<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use ICanBoogie\ActiveRecord\Query;

/**
 * A blueprint of a record tree.
 *
 * @see BlueprintNode
 */
class Blueprint
{
	/**
	 * Creates a {@link Blueprint} instance from an {@link ActiveRecord\Query}.
	 *
	 * @param Query $query
	 *
	 * @return Blueprint
	 */
	public static function from(Query $query)
	{
		$query->mode(\PDO::FETCH_CLASS, __NAMESPACE__ . '\BluePrintNode');

		$relation = array();
		$children = array();
		$index = array();

		foreach ($query as $row)
		{
			$row->parent = null;
			$row->depth = null;
			$row->children = array();

			$nid = $row->nid;
			$parent_id = $row->parentid;
			$index[$nid] = $row;
			$relation[$nid] = $parent_id;
			$children[$parent_id][$nid] = $nid;
		}

		$tree = array();

		foreach ($index as $nid => $page)
		{
			if (!$page->parentid || empty($index[$page->parentid]))
			{
				$tree[$nid] = $page;

				continue;
			}

			$page->parent = $index[$page->parentid];
			$page->parent->children[$nid] = $page;
		}

		self::set_depth($tree);

		return new static($query->model, $relation, $children, $index, $tree);
	}

	private static function set_depth(array $branch, $depth=0)
	{
		foreach ($branch as $node)
		{
			$node->depth = $depth;

			if (!$node->children)
			{
				continue;
			}

			self::set_depth($node->children, $depth + 1);
		}
	}

	/**
	 * The child/parent relation.
	 *
	 * An array where each key/value is the identifier of a node and the idenfier of its parent,
	 * or zero if the node has no parent.
	 *
	 * @var array[int]int
	 */
	public $relation;

	/**
	 * The parent/children relation.
	 *
	 * An array where each key/value is the identifier of a parent and an array made of the
	 * identifiers of its children. Each key/value pair of the children value is made of the
	 * child identifier.
	 *
	 * @var array[int]array
	 */
	public $children;

	/**
	 * Index of the blueprint nodes.
	 *
	 * Blueprint nodes are instances of the {@link BlueprintNode} class. The key of the index is
	 * the identifier of the node, while the value is the node instance.
	 *
	 * @var array[int]BlueprintNode
	 */
	public $index;

	/**
	 * Pages nested as a tree.
	 *
	 * @var array[int]BlueprintNode
	 */
	public $tree;

	/**
	 * Model associated with the blueprint.
	 *
	 * @var \ICanBoogie\ActiveRecord\Model
	 */
	public $model;

	/**
	 * The blueprint is usualy constructed by the {@link Model::blueprint()} method or the
	 * {@link subset()} method.
	 *
	 * @param Model $model
	 * @param array $relation The child/parent relations.
	 * @param array $children The parent/children relations.
	 * @param array $index Pages index.
	 * @param array $tree Pages nested as a tree.
	 */
	public function __construct(Model $model, array $relation, array $children, array $index, array $tree)
	{
		$this->relation = $relation;
		$this->children = $children;
		$this->index = $index;
		$this->tree = $tree;
		$this->model = $model;
	}

	/**
	 * Checks if a branch has children.
	 *
	 * @param int $nid Identifier of the branch.
	 *
	 * @return boolean
	 */
	public function has_children($nid)
	{
		return !empty($this->children[$nid]);
	}

	/**
	 * Returns the number of children of a branch.
	 *
	 * @param int $nid
	 *
	 * @return int
	 */
	public function children_count($nid)
	{
		return count($this->children[$nid]);
	}

	/**
	 * Create a subset of the blueprint.
	 *
	 * @param int $nid Identifier of the starting branch.
	 * @param int $depth Maximum depth of the subset.
	 * @param callable $filter The filter callback. Nodes are discarted when the filter returns
	 * true.
	 *
	 * @return Blueprint
	 */
	public function subset($nid=null, $depth=null, $filter=null)
	{
		$relation = array();
		$children = array();
		$index = array();

		$iterator = function(array $branch) use(&$iterator, &$filter, &$depth, &$relation, &$children, &$index)
		{
			$pages = array();

			foreach ($branch as $nid => $node)
			{
				$node_children = $node->children;
				$node = clone $node;
				$node->children = array();

				if ($node_children && ($depth === null || $node->depth < $depth))
				{
					$node->children = $iterator($node_children);
				}

				if ($filter && $filter($node))
				{
					continue;
				}

				$parentid = $node->parentid;

				$relation[$nid] = $parentid;
				$children[$parentid][] = $nid;
				$pages[$nid] = $node;
				$index[$nid] = $node;
			}

			return $pages;
		};

		$tree = $iterator($nid ? $this->index[$nid]->children : $this->tree);

		return new static($this->model, $relation, $children, $index, $tree);
	}

	/**
	 * Populates the blueprint by loading the associated records.
	 *
	 * The method adds the `record` property to the blueprint nodes.
	 *
	 * @return array[int]\Icybee\Modules\Pages\Page
	 */
	public function populate()
	{
		$records = $this->model->find(array_keys($this->index));

		foreach ($records as $nid => $record)
		{
			$this->index[$nid]->record = $record;
		}

		return $records;
	}
}

/**
 * A node of the blueprint.
 *
 * @see Blueprint
 */
class BlueprintNode
{
	/**
	 * The identifier of the page.
	 *
	 * @var int
	 */
	public $nid;

	/**
	 * Depth of the node is the tree.
	 *
	 * @var int
	 */
	public $depth;

	/**
	 * The identifier of the parent of the page.
	 *
	 * @var int
	 */
	public $parentid;

	/**
	 * Blueprint node of the parent of the page.
	 *
	 * @var BlueprintNode
	 */
	public $parent;

	/**
	 * The children of the node.
	 *
	 * @var array[int]BlueprintNode
	 */
	public $children;

	/**
	 * Inaccessible properties are obtained from the record.
	 *
	 * @param string $property
	 */
	public function __get($property)
	{
		return $this->record->$property;
	}

	/**
	 * Unknown method calls are forwarded to the record.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array($this->record, $method, $arguments);
	}
}