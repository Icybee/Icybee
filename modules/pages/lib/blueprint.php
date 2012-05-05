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
 * Blueprint of a pages hierarchy.
 */
class Blueprint
{
	public $relation;
	public $children;
	public $index;
	public $tree;
	public $model;

	/**
	 * The blueprint is usualy constructed by the {@link Model::get_blueprint()} method or the
	 * {@link subset()} method.
	 *
	 * @param Model $model
	 * @param array $relation The child/parent relation.
	 * @param array $children The parent/children relation.
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
	 * @param callable $filter The filter callback. Pages are discarted when the filter returns
	 * true.
	 * @param int $depth Maximum depth of the subset.
	 * @param int $nid Identifier of the starting branch.
	 *
	 * @return \ICanBoogie\Modules\Pages\Blueprint
	 */
	public function subset($filter=null, $depth=null, $nid=null)
	{
		$relation = array();
		$children = array();
		$index = array();

		$iterator = function(array $branches) use(&$iterator, &$filter, &$depth, &$relation, &$children, &$index)
		{
			$pages = array();

			foreach ($branches as $nid => $branch)
			{
				if ($filter && $filter($branch))
				{
					continue;
				}

				$branch_children = $branch->children;
				$branch = clone $branch;
				$branch->children = array();

				if ($branch_children && ($depth === null || $branch->depth < $depth))
				{
					$branch->children = $iterator($branch_children);
				}

				$parentid = $branch->parentid;

				$relation[$nid] = $parentid;
				$children[$parentid][] = $nid;
				$pages[$nid] = $branch;
				$index[$nid] = $branch;
			}

			return $pages;
		};

		$tree = $iterator($nid ? $this->index[$nid]->children : $this->tree);

		return new self($this->model, $relation, $children, $index, $tree);
	}

	/**
	 * Populates the blueprint by loading the associated records.
	 *
	 * The method modify the branches for the blueprint with the `record` property.
	 *
	 * @return array[int]\ICanBoogie\ActiveRecord\Page
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