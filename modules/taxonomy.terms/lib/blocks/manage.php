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

class ManageBlock extends \Icybee\ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes += array
			(
				self::T_ORDER_BY => array('term', 'asc')
			)
		);
	}

	/**
	 * Adds the following columns:
	 *
	 * - `term`: An instance of {@link ManageBlock\TermColumn}.
	 * - `vid`: An instance of {@link ManageBlock\VidColumn}.
	 * - `popularity`: An instance of {@link ManageBlock\PopularityColumn}.
	 */
	protected function get_available_columns()
	{
		return array_merge(parent::get_available_columns(), array
		(
			'term' => __CLASS__ . '\TermColumn',
			'vid' => __CLASS__ . '\VidColumn',
			'popularity' => __CLASS__ . '\PopularityColumn'
		));
	}
}

namespace Icybee\Modules\Taxonomy\Terms\ManageBlock;

use ICanBoogie\ActiveRecord\Query;

use Icybee\ManageBlock\Column;
use Icybee\ManageBlock\FilterDecorator;
use Icybee\ManageBlock\EditDecorator;

class TermColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'title' => 'Term'
			)
		);
	}

	public function render_cell($record)
	{
		return new EditDecorator($record->term, $record);
	}
}

/**
 * Representation of the `vid` column.
 */
class VidColumn extends Column
{
	public function __construct(\Icybee\Modules\Taxonomy\Terms\ManageBlock $manager, $id, array $options=array())
	{
		global $core;

		parent::__construct
		(
			$manager, $id, $options + array
			(
				'title' => 'Vocabulary',
				'orderable' => true
			)
		);
	}

	/**
	 * Extends the "vid" column by providing vocabulary filters.
	 *
	 * @param array $column
	 * @param string $id
	 */
	protected function get_options()
	{
		global $core;

		// Move this to render_header() when it's actually used

		$keys = $this->manager->module->model->select('DISTINCT vid')->all(\PDO::FETCH_COLUMN);

		if (count($keys) < 2)
		{
			$this->orderable = false;

			return;
		}

		// /

		return $core->models['taxonomy.vocabulary']
		->select('CONCAT("?vid=", vid), vocabulary')
		->where(array('vid' => $keys))
		->order('vocabulary')
		->pairs;
	}

	/**
	 * Alters the query with the 'vid' filter.
	 */
	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			$query->filter_by_vid($filter_value);
		}

		return $query;
	}

	/**
	 * Orders the records according to vocabulary name.
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		global $core;

		$names = $core->models['taxonomy.vocabulary']->select('vid, vocabulary')->order("vocabulary " . ($order_direction < 0 ? 'DESC' : 'ASC'))->pairs;

		return $query->order('vid', array_keys($names));
	}

	public function render_cell($record)
	{
		return new FilterDecorator($record, $this->id, $this->manager->is_filtering($this->id), $record->vocabulary);
	}
}

/**
 * Representation of the `popularity` column.
 *
 * The column displays the times a term is associated to a node.
 */
class PopularityColumn extends Column
{
	/**
	 * Popularity values for the displayed rows.
	 *
	 * @var array[int]int
	 */
	private $values;

	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, array
			(
				'title' => 'Popularity',
				'class' => 'pull-right',
				'orderable' => true
			)
		);
	}

	/**
	 * Computes the popularity of the specified records.
	 *
	 * Note: The popularity values are stored in the {@link $values} property.
	 */
	public function alter_records(array $records)
	{
		$keys = array();

		foreach ($records as $record)
		{
			$keys[] = $record->vtid;
		}

		if ($keys)
		{
			$this->values = $this->manager->module->model('nodes')->filter_by_vtid($keys)->count('vtid');
		}

		return $records;
	}

	/**
	 * Orders the records according to their popularity.
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $query->order("(SELECT COUNT(vtid) FROM {self}__nodes WHERE vtid = term.vtid) " . ($order_direction < 0 ? 'DESC' : 'ASC'));
	}

	public function render_cell($record)
	{
		return isset($this->values[$record->vtid]) ? $this->values[$record->vtid] : 0;
	}
}