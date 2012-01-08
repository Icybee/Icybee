<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Views\Contents;

use ICanBoogie\ActiveRecord\Query;

class Provider extends \Icybee\Views\Nodes\Provider
{
	/**
	 * Support for the `year`, `month` and `day` conditions. Changes the order to
	 * `date DESC, created DESC`.
	 *
	 * If the view is of type "home" the query is altered to search for nodes which are not
	 * excluded from _home_.
	 *
	 * @see Icybee\Views\Nodes\Provider::alter_query()
	 */
	protected function alter_query(Query $query, array $conditions)
	{
		if (!empty($conditions['year']))
		{
			$query->where('YEAR(date) = ?', $conditions['year']);
		}

		if (!empty($conditions['month']))
		{
			$query->where('MONTH(date) = ?', $conditions['month']);
		}

		if (!empty($conditions['day']))
		{
			$query->where('DAY(date) = ?', $conditions['day']);
		}

		if ($this->view->type == 'home')
		{
			$query->where('is_home_excluded = 0');
		}

		return parent::alter_query($query, $conditions)->order('date DESC, created DESC');
	}

	protected function alter_context(array $context)
	{
		$context = parent::alter_context($context);

		if ($this->view->type == 'list')
		{
			$context['range'] = array
			(

			);
		}

		return $context;
	}
}