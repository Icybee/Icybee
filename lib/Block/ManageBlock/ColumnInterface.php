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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;

/**
 * An interface to implement manager columns.
 */
interface ColumnInterface
{
	const ORDER_ASC = 1;
	const ORDER_DESC = -1;

	/**
	 * Update the query conditions according to the specified modifiers.
	 *
	 * @param array $conditions The previous filters.
	 * @param array $modifiers The filters modifiers.
	 *
	 * @return array The updated conditions.
	 */
	public function alter_conditions(array &$conditions, array $modifiers);

	/**
	 * Alter the initial query.
	 *
	 * @param Query $query The query to alter.
	 *
	 * @return Query The altered query.
	 */
	public function alter_query(Query $query);

	/**
	 * Alter the query according to the filter value specified.
	 *
	 * The method is only invoked if the filter value is not `null`.
	 *
	 * @param Query $query The query to alter.
	 * @param mixed $value The value of the filter.
	 *
	 * @return Query The altered query.
	 */
	public function alter_query_with_value(Query $query, $value);

	/**
	 * Alter the order in which records are fetched.
	 *
	 * The method is only invoked if the column is used to order the records.
	 *
	 * @param Query $query
	 * @param int $order_direction The order is descending if the direction is inferior to zero,
	 * ascending otherwise.
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction);

	/**
	 * Alter the fetched records.
	 *
	 * @param ActiveRecord[] $records The records to alter.
	 *
	 * @return ActiveRecord[] The altered records.
	 */
	public function alter_records(array &$records);

	/**
	 * Renders the column's header.
	 *
	 * @return string
	 */
	public function render_header();

	/**
	 * Renders a column cell.
	 *
	 * @param ActiveRecord $record
	 *
	 * @return string
	 */
	public function render_cell($record);
}
