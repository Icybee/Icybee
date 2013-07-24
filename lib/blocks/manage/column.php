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

use Brickrouge\DropdownMenu;
use Brickrouge\Element;

/**
 * Representation of a column of the manager element.
 *
 * This is the base class for all the columns.
 *
 * @property-read mixed $filter_value The value used by the column to filter the records.
 * @property-read bool $is_filtering `true` if the column is currently filtering the records.
 * `false` otherwise.
 *
 * @todo-20130627:
 *
 * - Rename `label` property as `header`.
 */
class Column extends \ICanBoogie\Object
{
	const ORDER_ASC = 1;
	const ORDER_DESC = -1;

	public $id;

	public $title;
	public $class;
	public $filters;
	public $reset;
	public $orderable = false;
	public $order;
	public $default_order = self::ORDER_ASC;
	public $discreet = true;

	protected $header_renderer = 'Icybee\ManageBlock\HeaderRenderer';
	protected $cell_renderer = 'Icybee\ManageBlock\CellRenderer';

	public $manager;

	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		// TODO-20130627

		if (method_exists($this, 'update_filters'))
		{
			throw new \Exception("The <q>update_filters()</q> method is deprecated, please use the <q>alter_filters()</q> method.");
		}

		if (method_exists($this, 'alter_query'))
		{
			throw new \Exception("The <q>alter_query()</q> method is deprecated, please use the <q>alter_query_with_filter()</q> method.");
		}

		// /

		$this->manager = $manager;
		$this->id = $id;

		$this->modify_options($options + $this->resolve_default_values());
	}

	// TODO-20130627: remove this compat method
	protected function volatile_set_filtering()
	{
		throw new \InvalidArgumentException("The <q>filtering</q> property is deprecated. Use <q>is_filtering</q> or <q>filter_value</q>");
	}

	protected function volatile_set_label($value)
	{
		trigger_error("The <q>label</q> property is deprecated, use <q>title</q> instead.");

		$this->title = $value;
	}

	protected function volatile_get_label()
	{
		trigger_error("The <q>label</q> property is deprecated, use <q>title</q> instead.");

		return $this->title;
	}

	/**
	 * Returns `true` if the column is filtering the records.
	 *
	 * @return boolean
	 */
	protected function volatile_get_is_filtering()
	{
		return $this->manager->is_filtering($this->id);
	}

	/**
	 * Returns the value used by the column to filter the records.
	 *
	 * @return mixed|null
	 */
	protected function volatile_get_filter_value()
	{
		return $this->is_filtering ? $this->manager->options->filters[$this->id] : null;
	}

	/**
	 * Translates and formats the specified string.
	 *
	 * @param string $native
	 * @param array $args
	 * @param array $options
	 *
	 * @return string
	 */
	public function t($native, array $args=array(), array $options=array())
	{
		return $this->manager->t($native, $args, $options);
	}

	/**
	 * Returns the default values for the column initialization.
	 *
	 * @return array
	 */
	protected function resolve_default_values()
	{
		$id = $this->id;
		$fields = $this->manager->model->extended_schema['fields'];
		$field = isset($fields[$id]) ? $fields[$id] : null;

		$orderable = true;
		$default_order = 1;
		$discreet = false;

		if ($field)
		{
			if (($field['type'] == 'integer' && (!empty($field['primary']) || !empty($field['indexed']))) || $field['type'] == 'boolean')
			{
				$orderable = false;

				if (!empty($field['indexed']))
				{
					$discreet = true;
				}
			}

			if (in_array($field['type'], array('date', 'datetime', 'timestamp')))
			{
				$default_order = -1;
			}
		}
		else
		{
			$orderable = false;
		}

		return array
		(
			'title' => $id,
			'reset' => "?$id=",
			'orderable' => $orderable,
			'default_order' => $default_order
		);
	}

	/**
	 * Modifies the options of the column.
	 *
	 * @param array $options
	 *
	 * @return \Icybee\ManageBlock\Column
	 */
	public function modify_options(array $options)
	{
		foreach ($options as $option => $value)
		{
			switch ($option)
			{
				case 'label':
				case 'title':
				case 'class':
				case 'filters':
				case 'reset':
				case 'orderable':
				case 'order':
				case 'default_order':
				case 'discreet':
				case 'filtering':
				case 'header_renderer':
				case 'cell_renderer':
					$this->$option = $value;
					break;

				case 'hook':
// 					var_dump($value);
					break;
			}
		}

		return $this;
	}

	/**
	 * Updates the filters for the records according to the specified modifiers.
	 *
	 * Note: The filters are returned as is, subclasses shoudl override the method according to
	 * their needs.
	 *
	 * @param array $filters
	 * @param array $modifiers
	 *
	 * @return array The updated filters.
	 */
	public function alter_filters(array $filters, array $modifiers)
	{
		return $filters;
	}

	/**
	 * Alters the query according to the filter value specified.
	 *
	 * The method does a simple `{$this->id} = {$filter_value}`, subclasses might want to override
	 * the method according to the kind of filter they provide.
	 *
	 * @param Query $query
	 * @param mixed $filter_value
	 *
	 * @return Query
	 */
	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			$query->and(array($this->id => $filter_value));
		}

		return $query;
	}

	/**
	 * Alters the ORDER clause of the query according to the column identifier and the order
	 * direction.
	 *
	 * The implementation of the method is simple, subclasses might want to override the method
	 * to support complexer ordering.
	 *
	 * @param Query $query
	 * @param int $order_direction
	 *
	 * @return Query
	 */
	public function alter_query_with_order(Query $query, $order_direction)
	{
		return $query->order("`$this->id` " . ($order_direction < 0 ? 'desc' : 'asc'));
	}

	/**
	 * Alters the records.
	 *
	 * Note: The records are returned as is, subclasses might override the method according to
	 * their needs.
	 *
	 * @param array $records
	 *
	 * @return array[]ActiveRecord
	 */
	public function alter_records(array $records)
	{
		return $records;
	}

	/**
	 * Returns the options available for the filter.
	 *
	 * @return array|null
	 */
	protected function get_options()
	{
		if (empty($this->filters['options']))
		{
			return;
		}

		$options = array();

		foreach ($this->filters['options'] as $qs => $label)
		{
			if ($qs[0] == '=')
			{
				$qs = $this->id . $qs;
			}

			$options['?' . $qs] = $this->manager->t($label);
		}

		return $options;
	}

	/**
	 * Renders the column's options.
	 */
	public function render_options()
	{
		$options = $this->get_options();

		if (!$options)
		{
			return;
		}

		if ($this->is_filtering)
		{
			$options = array_merge
			(
				array
				(
					$this->reset => $this->t('Display all'),
					false
				),

				$options
			);
		}

		$menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $this->filter_value
			)
		);

		return <<<EOT
<div class="dropdown navbar"><a href="#" data-toggle="dropdown"><i class="icon-cog"></i></a>$menu</div>
EOT;
	}

	/**
	 * Renders the column's header.
	 *
	 * @return string
	 */
	public function render_header()
	{
		$renderer = $this->header_renderer;

		if (!($renderer instanceof HeaderRenderer))
		{
			$this->header_renderer = $renderer = new $renderer($this);
		}

		return $renderer();
	}

	/**
	 * Renders a column cell.
	 *
	 * @param mixed $record
	 *
	 * @return string
	 */
	public function render_cell($record)
	{
		$renderer = $this->cell_renderer;

		if (!($renderer instanceof CellRenderer))
		{
			$this->cell_renderer = $renderer = new $renderer($this);
		}

		return $renderer($record, $this->id);
	}

	/**
	 * Adds assets to the document.
	 *
	 * Subclasses might implement this method to add assets to the document.
	 *
	 * @param \Brickrouge\Document $document
	 */
	public function add_assets(\Brickrouge\Document $document)
	{

	}
}

/**
 * Default header renderer.
 */
class HeaderRenderer
{
	protected $column;

	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	public function __invoke()
	{
		$column = $this->column;
		$module = $this->column->manager->module;
		$id = $column->id;
		$title = $column->title;
		$t = $this->column->manager->t;

		if ($title)
		{
			$title = $t($id, array(), array('scope' => 'column', 'default' => $title));
		}

		if ($column->is_filtering)
		{
			$a_title = $t('View all');
			$title = $title ?: '&nbsp;';

			return <<<EOT
<a href="{$column->reset}" title="{$a_title}"><span class="title">{$title}</span></a>
EOT;
		}

		if ($title && $column->orderable)
		{
			$order = $column->order;
			$order_reverse = ($order === null) ? $column->default_order : -$order;

			return new Element
			(
				'a', array
				(
					Element::INNER_HTML => '<span class="title">' . $title . '</span>',

					'title' => $t('Sort by: :identifier', array(':identifier' => $title)),
					'href' => "?order=$id:" . ($order_reverse < 0 ? 'desc' : 'asc'),
					'class' => $order ? ($order < 0 ? 'desc' : 'asc') : null
				)
			);
		}

		return $title;
	}
}

/**
 * Default cell renderer.
 */
class CellRenderer
{
	protected $column;

	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	public function __invoke($record, $property)
	{
		return \Brickrouge\escape($record->$property);
	}

	public function t($str, array $args=array(), array $options=array())
	{
		return $this->column->t($str, $args, $options);
	}
}