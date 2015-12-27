<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Facets\Criterion;
use Icybee\Block\ManageBlock;

/**
 * @property-read Criterion $criterion
 * @property-read ManageBlock $manager
 * @property-read string $id
 */
trait CriterionColumnTrait
{
	protected function get_criterion()
	{
		return $this->manager->model->criterion_list[$this->id];
	}

	/**
	 * Forwards the method to {@link Criterion::alter_conditions}.
	 *
	 * @param array $conditions
	 * @param array $modifiers
	 *
	 * @return array
	 */
	public function alter_conditions(array &$conditions, array $modifiers)
	{
		$this->criterion->alter_conditions($conditions, $modifiers);
	}

	/**
	 * Forwards the method to {@link Criterion::alter_query}.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	public function alter_query(Query $query)
	{
		return $this->criterion->alter_query($query);
	}

	/**
	 * Forwards the method to {@link Criterion::alter_query_with_value}.
	 *
	 * @param Query $query
	 * @param $filter_value
	 *
	 * @return Query
	 */
	public function alter_query_with_value(Query $query, $filter_value)
	{
		return $this->criterion->alter_query_with_value($query, $filter_value);
	}

	/**
	 * Forwards the method to {@link Criterion::alter_query_with_order}.
	 *
	 * @param Query $query
	 * @param $order_direction
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $this->criterion->alter_query_with_order($query, $order_direction);
	}

	/**
	 * Forwards the method to {@link Criteion::alter_records}.
	 *
	 * @param array $records
	 */
	public function alter_records(array &$records)
	{
		$this->criterion->alter_records($records);
	}
}
