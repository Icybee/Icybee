<?php

namespace Icybee\ManageBlock;

use ICanBoogie\ActiveRecord;
use ICanBoogie\PropertyNotReadable;

class Column
{
	const DISCREET_PLACEHOLDER = '<span class="lighter">―</span>';

	protected $manager;
	protected $id;
	protected $options = array();

	/**
	 * The renderer used to render the head of the column.
	 *
	 * @var HeadRenderer
	 */
	protected $head_renderer;

	/**
	 * The renderer used to render the cells of the column.
	 *
	 * @var CellRenderer
	 */
	protected $cell_renderer;

	/**
	 * Initializes the {@link $manager}, {@link $id} and {@link $options} properties.
	 *
	 * The options are passed to the {@link change} method.
	 *
	 * @param \Icybee\ManageBlock $manager
	 * @param string $id
	 * @param array $options
	 */
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options)
	{
		$this->manager = $manager;
		$this->id = $id;

		$this->change
		(
			$options + array
			(
				'title' => null,
				'class' => null,

				'filters' => null,
				'filtering' => isset($manager->options['filters'][$id]), // TODO-20130425: use a `is_filtering($id)` method
				'reset' => "?$id=",

				'orderable' => false,
				'order' => null,
				'default_order' => null,

				'discreet' => false,

				'head_renderer' => null,
				'cell_renderer' => null
			)
		);
	}

	public function __get($property)
	{
		static $getters = array('manager');

		if (in_array($property, $getters))
		{
			return $this->$property;
		}

		throw new PropertyNotReadable(array($property, $this));
	}

	public function change(array $options)
	{
		$options += array
		(
			'title' => null,
			'class' => null,

			'filters' => null,
			'filtering' => null,
			'reset' => null,

			'orderable' => false,
			'order' => null,
			'default_order' => null,

			'discreet' => false,

			'head_renderer' => null,
			'cell_renderer' => null
		);
	}

	public function alter_records(array $records)
	{

	}

	public function render_head()
	{

	}

	protected $last_rendered_cell_value;

	public function render_cell(ActiveRecord $record, $property)
	{
		$value = $record->$property;

		if ($this->column->is_discreet && $value == $this->last_rendered_cell_value)
		{
			return static::DISCREET_PLACEHOLDER;
		}

		$this->last_rendered_cell_value = $value;

		return call_user_func($this->cell_renderer, $record, $property);
	}
}

/**
 * Renderer for a column head.
 */
class HeadRenderer
{
	/**
	 * The parent column.
	 *
	 * @var Column
	 */
	protected $column;

	public function __construct(Column $column)
	{
		$this->column = $column;
	}
}

/**
 * Basic renderer for a column cell.
 */
class CellRenderer
{
	/**
	 * The parent column.
	 *
	 * @var Column
	 */
	protected $column;

	/**
	 *
	 * @param Column $column
	 */
	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	public function __invoke(ActiveRecord $record, $property)
	{
		return $record->$property;
	}
}