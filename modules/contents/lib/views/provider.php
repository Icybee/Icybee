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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;

class Provider extends \Icybee\Views\Nodes\Provider
{
	public function __invoke()
	{
		$rc = parent::__invoke();

		if ($this->get_return_type() == self::RETURN_ONE && !$rc)
		{
			$rc = $this->rescue();
		}

		return $rc;
	}

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

	/**
	 * Rescues a missing record by providing the best matching one.
	 *
	 * Match is computed from the slug of the module's own visible records, thus rescue if only
	 * triggered if 'slug' is defined in the conditions.
	 *
	 * @return ActiveRecord\Content|null The record best matching the condition slug, or null if
	 * none was similar enough.
	 */
	protected function rescue()
	{
		$conditions = $this->conditions;

		if (!empty($conditions['nid']) || empty($conditions['slug']))
		{
			return;
		}

		$slug = $conditions['slug'];
		$model = $this->module->model;
		$tries = $model->select('nid, slug')->own->visible->order('date DESC')->pairs;
		$key = null;
		$max = 0;

		foreach ($tries as $nid => $compare)
		{
			similar_text($slug, $compare, $p);

			if ($p > $max)
			{
				$key = $nid;

				if ($p > 90)
				{
					break;
				}

				$max = $p;
			}
		}

		if ($key)
		{
			$record = $model[$key];

			wd_log_done('The record %title was rescued!', array('title' => $record->title));

			//TODO-20120109: should we redirect to the correct record URL ?

			return $record;
		}
	}
}