<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Terms;

use ICanBoogie\ActiveRecord\Query;

class ManageBlock extends \WdManager
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes += array
			(
				self::T_KEY => 'vtid'
			)
		);
	}

	protected function columns()
	{
		return array
		(
			'term' => array
			(
				'label' => 'Name'
			),

			'vid' => array
			(
				'label' => 'Vocabulary'
			),

			'popularity' => array
			(
				'label' => 'Popularity'
			)
		);
	}

	protected function update_options(array $options, array $modifiers)
	{
		$options = parent::update_options($options, $modifiers);

		if (isset($modifiers['by']) && $modifiers['by'] == 'popularity')
		{
			$options['order_by'] = 'popularity';
			$options['order_direction'] = strtolower($modifiers['order']) == 'desc' ? 'desc' : 'asc';
		}

		return $options;
	}

	/**
	 * Alters the query with the 'vid' filter.
	 *
	 * @see Icybee.Manager::alter_query()
	 */
	protected function alter_query(Query $query, array $filters)
	{
		$query = parent::alter_query($query, $filters);

		if (isset($filters['vid']))
		{
			$query->filter_by_vid($filters['vid']);
		}

		return $query;
	}

	protected function alter_range_query(Query $query, array $options)
	{
		$query->select('*, (select count(s1.nid) from {self}__nodes as s1 where s1.vtid = term.vtid) AS `popularity`');
		$query->mode(\PDO::FETCH_CLASS, 'Icybee\Modules\Taxonomy\Terms\Term', array($this->model));

		return parent::alter_range_query($query, $options);
	}

	/*
	 * Columns
	 */

	/**
	 * Extends the "vid" column by providing vocabulary filters.
	 *
	 * @param array $column
	 * @param string $id
	 */
	protected function extend_column_vid(array $column, $id, array $fields)
	{
		global $core;

		$keys = $this->module->model->select('DISTINCT vid')->all(\PDO::FETCH_COLUMN);

		if (!$keys || count($keys) == 1)
		{
			return array
			(
				'sortable' => false
			)

			+ parent::extend_column($column, $id, $fields);
		}

		$vocabulary = $core->models['taxonomy.vocabulary']->select('CONCAT("=", vid), vocabulary')->where(array('vid' => $keys))->order('vocabulary')->pairs;

		return array
		(
			'filters' => array
			(
				'options' => $vocabulary
			)
		)

		+ parent::extend_column($column, $id, $fields);
	}

	/*
	 * Cells
	 */

	protected function get_cell_term($record, $property)
	{
		$label = $record->term;

		return self::modify_code($label, $record->vtid, $this);
	}

	private $last_rendered_vid;

	protected function get_cell_vid($record, $property)
	{
		$vid = $record->vid;

		if ($this->last_rendered_vid === $vid)
		{
			return self::REPEAT_PLACEHOLDER;
		}

		$this->last_rendered_vid = $vid;

		return parent::render_filter_cell($record, $property, $record->vocabulary);
	}

	private $last_rendered_popularity;

	protected function get_cell_popularity($record, $property)
	{
		$popularity = $record->$property;

		if ($this->last_rendered_popularity === $popularity)
		{
			return self::REPEAT_PLACEHOLDER;
		}

		return $this->last_rendered_popularity = $popularity;
	}
}