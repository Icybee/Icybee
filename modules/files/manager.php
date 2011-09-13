<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Manager;

use ICanBoogie\ActiveRecord\File;
use ICanBoogie\ActiveRecord\Query;

class Files extends Nodes
{
	public function __construct($module, array $tags=array())
	{
		global $core;

		parent::__construct($module, $tags);

		$core->document->css->add('public/manage.css');
	}

	protected function columns()
	{
		return parent::columns() + array
		(
			File::MIME => array
			(

			),

			File::SIZE => array
			(
				'class' => 'size'
			)
		);
	}

	protected function extend_column_size(array $column, $id, array $fields)
	{
		if ($this->count < 10 && !$this->options['filters'])
		{
			return parent::extend_column($column, $id, $fields);
		}

		return array
		(
			'filters' => array
			(
				'options' => array
				(
					'=b' => 'Big',
					'=m' => 'Medium',
					'=s' => 'Small'
				)
			)
		)

		+ parent::extend_column($column, $id, $fields);
	}

	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['size']))
		{
			$value = $modifiers['size'];

			if (in_array($value, array('b', 'm', 's')))
			{
				$filters['size'] = $value;
			}
			else
			{
				unset($filters['size']);
			}
		}

		return $filters;
	}

	/**
	 * Override the method to unset the 'size' filter if it is set, because we want a range not
	 * an exact value.
	 *
	 * @see Icybee.Manager::get_query_conditions()
	 */
	protected function get_query_conditions(array $options)
	{
		unset($options['filters']['size']);

		return parent::get_query_conditions($options);
	}

	protected function alter_query(Query $query, array $filters)
	{
		$query = parent::alter_query($query, $filters);

		if (isset($filters['size']))
		{
			list($avg, $max, $min) = $this->model->similar_site->select('AVG(size), MAX(size), MIN(size)')->one(PDO::FETCH_NUM);

			$bounds = array
			(
				$min,
				round($avg - ($avg - $min) / 3),
				round($avg),
				round($avg + ($max - $avg) / 3),
				$max
			);

			switch ($filters['size'])
			{
				case 'b': $query->where('size >= ?', $bounds[3]); break;
				case 'm': $query->where('size >= ? AND size < ?', $bounds[2], $bounds[3]); break;
				case 's': $query->where('size < ?', $bounds[2]); break;
			}
		}

		return $query;
	}

	protected function render_cell_title($record, $property)
	{
		$rc  = '<a class="download" href="' . wd_entities($record->url('download')) . '"';
		$rc .= ' title="' . t('Download the file: :path', array(':path' => $record->path)) . '"';
		$rc .= '>';
		$rc .= 'download</a>';

		$rc .= parent::render_cell_title($record, $property);

		return $rc;
	}

	protected function render_cell_mime($record, $property)
	{
		return parent::render_filter_cell($record, $property);
	}
}