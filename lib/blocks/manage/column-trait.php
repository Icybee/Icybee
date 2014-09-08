<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\ManageBlock;

use ICanBoogie\ActiveRecord\Query;

/**
 * Implement some function of the {@link ColumnInterface} interface.
 */
trait ColumnTrait
{
	/**
	 * The filters are returned as is, subclasses should override the method according to
	 * their needs.
	 */
	public function alter_filters(array $filters, array $modifiers)
	{
		return $filters;
	}

	/**
	 * The query is returned as is, subclasses should override the method according to
	 * their needs.
	 */
	public function alter_query(Query $query)
	{
		return $query;
	}

	/**
	 * The method does a simple `{$this->id} = {$filter_value}`, subclasses might want to override
	 * the method according to the kind of filter they provide.
	 */
	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			$query->and([ $this->id => $filter_value ]);
		}

		return $query;
	}

	/**
	 * The implementation of the method is simple, subclasses might want to override the method
	 * to support complexer ordering.
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $query->order("`$this->id` " . ($order_direction < 0 ? 'DESC' : 'ASC'));
	}

	/**
	 * The records are returned as is, subclasses might override the method according to
	 * their needs.
	 */
	public function alter_records(array $records)
	{
		return $records;
	}
}