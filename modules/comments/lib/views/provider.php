<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Comments;

use ICanBoogie\ActiveRecord\Query;

class Provider extends \Icybee\Views\ActiveRecord\Provider
{
	protected function alter_conditions(array $conditions)
	{
		if (isset($this->context['this']) && $this->context['this'] instanceof \ICanBoogie\ActiveRecord\Node)
		{
			$conditions['nid'] = $this->context['this']->nid;
		}

		return $conditions;
	}

	protected function alter_query(Query $query, array $conditions)
	{
		if (isset($conditions['nid']))
		{
			$query->where('nid = ?', (int) $conditions['nid']);
		}

		$query->where('status != "spam" && status != "pending"');

		return $query->order('created');
	}

	protected function alter_context(array $context, Query $query, array $conditions)
	{
		$count = $query->count;

		$context['self']['count'] = t(':count comments', array(':count' => $count));

		return $context;
	}
}