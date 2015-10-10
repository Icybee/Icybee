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

use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Prototyped;

use Brickrouge\DropdownMenu;

use Icybee\Block\ManageBlock;

/**
 * Representation of a column of the manager element.
 *
 * This is the base class for all the columns.
 *
 * @property-read mixed $filter_value The value used by the column to filter the records.
 * @property-read bool $is_filtering `true` if the column is currently filtering the records.
 * `false` otherwise.
 * @property-read \ICanBoogie\Core|\Icybee\Binding\CoreBindings $app
 */
class Column extends Prototyped implements ColumnInterface
{
	use ColumnTrait;

	public $id;

	public $title;
	public $class;
	public $filters;
	public $reset;
	public $orderable = false;
	public $order;
	public $default_order = self::ORDER_ASC;
	public $discreet = true;

	protected $header_renderer = HeaderRenderer::class;
	protected $cell_renderer = CellRenderer::class;

	public $manager;

	public function __construct(ManageBlock $manager, $id, array $options = [])
	{
		$this->manager = $manager;
		$this->id = $id;

		$this->modify_options($options + $this->resolve_default_values());
	}

	/**
	 * Returns `true` if the column is filtering the records.
	 *
	 * @return boolean
	 */
	protected function get_is_filtering()
	{
		return $this->manager->is_filtering($this->id);
	}

	/**
	 * Returns the value used by the column to filter the records.
	 *
	 * @return mixed|null
	 */
	protected function get_filter_value()
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
	public function t($native, array $args = [], array $options = [])
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
		$schema = $this->manager->model->extended_schema;
		$field = isset($schema[$id]) ? $schema[$id] : null;

		$orderable = true;
		$default_order = 1;

		if ($field)
		{
			if (($field->type == SchemaColumn::TYPE_INTEGER && ($field->primary || $field->indexed))
			|| $field->type == SchemaColumn::TYPE_BOOLEAN)
			{
				$orderable = false;
			}

			if (in_array($field->type, [ 'date', 'datetime', 'timestamp' ]))
			{
				$default_order = -1;
			}
		}
		else
		{
			$orderable = false;
		}

		return  [

			'title' => $id,
			'reset' => "?$id=",
			'orderable' => $orderable,
			'default_order' => $default_order

		];
	}

	/**
	 * Modifies the options of the column.
	 *
	 * @param array $options
	 *
	 * @return $this
	 */
	public function modify_options(array $options)
	{
		static $valid_options = [

			'label',
			'title',
			'class',
			'filters',
			'reset',
			'orderable',
			'order',
			'default_order',
			'discreet',
			'filtering',
			'header_renderer',
			'cell_renderer'

		];

		foreach ($options as $option => $value)
		{
			if (!in_array($option, $valid_options))
			{
				\ICanBoogie\log_error("Invalid option: %option for column %column.", [

					'option' => $option,
					'column' => $this->manager->module . '.' . $this->id

				]);

				continue;
			}

			$this->$option = $value;
		}

		return $this;
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
			return null;
		}

		$options = [];

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
			return null;
		}

		if ($this->is_filtering)
		{
			$options = array_merge([

				$this->reset => $this->t('Display all'),
				false

			], $options);
		}

		$menu = new DropdownMenu([

			DropdownMenu::OPTIONS => $options,

			'value' => $this->filter_value

		]);

		return <<<EOT
<div class="dropdown navbar"><a href="#" data-toggle="dropdown"><i class="icon-cog"></i></a>$menu</div>
EOT;
	}

	public function render_header()
	{
		$renderer = $this->header_renderer;

		if (!($renderer instanceof HeaderRenderer))
		{
			$this->header_renderer = $renderer = new $renderer($this);
		}

		return $renderer();
	}

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
