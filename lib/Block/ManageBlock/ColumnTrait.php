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
 * Implement some function of the {@link ColumnInterface} interface.
 *
 * @property string $id
 */
trait ColumnTrait
{
	/**
	 * The conditions are returned as is, subclasses should override the method according to
	 * their needs.
	 *
	 * @param array $conditions
	 * @param array $modifiers
	 *
	 * @return array
	 */
	public function alter_conditions(array &$conditions, array $modifiers)
	{
		return $conditions;
	}

	/**
	 * The query is returned as is, subclasses should override the method according to
	 * their needs.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	public function alter_query(Query $query)
	{
		return $query;
	}

	/**
	 * The method does a simple `{$this->id} = {$filter_value}`, subclasses might want to override
	 * the method according to the kind of filter they provide.
	 *
	 * @param Query $query
	 * @param mixed $value
	 *
	 * @return Query
	 */
	public function alter_query_with_value(Query $query, $value)
	{
		if ($value)
		{
			$query->and([ $this->id => $value ]);
		}

		return $query;
	}

	/**
	 * The implementation of the method is simple, subclasses might want to override the method
	 * to support complexer ordering.
	 *
	 * @param Query $query
	 * @param $order_direction
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $query->order("`$this->id` " . ($order_direction < 0 ? 'DESC' : 'ASC'));
	}

	/**
	 * The records are returned as is, subclasses might override the method according to
	 * their needs.
	 *
	 * @param ActiveRecord[] $records
	 *
	 * @return ActiveRecord[]
	 */
	public function alter_records(array &$records)
	{
		return $records;
	}
}
