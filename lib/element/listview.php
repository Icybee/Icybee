<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

/**
 * A listview element.
 */
class ListView extends Element
{
	const COLUMNS = '#listview-columns';
	const ENTRIES = '#listview-entries';

	/**
	 * Columns use to display the data of the records.
	 *
	 * @var array[string]ListViewColumn
	 */
	protected $columns;

	public function __construct(array $attributes=[])
	{
		unset($this->columns);

		parent::__construct('div', $attributes);
	}

	/**
	 * Adds the following class names:
	 *
	 * - `listview`
	 *
	 * @inheritdoc
	 */
	protected function alter_class_names(array $class_names)
	{
		return parent::alter_class_names($class_names) + [

			'listview' => true

		];
	}

	/**
	 * Returns the columns of the listview.
	 *
	 * @return \Icybee\ListView\Column
	 */
	protected function get_columns()
	{
		$columns = $this[self::COLUMNS];
		$columns = $this->resolve_columns($columns);

		return $columns;
	}

	protected function resolve_columns(array $columns)
	{
		$resolved_columns = $columns;

		foreach ($resolved_columns as $id => &$column)
		{
			if (is_string($column))
			{
				$construct = $column;
				$column = new $construct($this, $id, []);
			}
		}

		return $resolved_columns;
	}

	/**
	 * Returns the entries to display.
	 *
	 * @return array[]mixed
	 */
	protected function get_entries()
	{
		return $this[self::ENTRIES];
	}

	protected function render_inner_html()
	{
		$head = $this->render_head();
		$foot = $this->render_foot();
		$body = $this->render_body();

		return <<<EOT
<table>
	$head
	$foot
	$body
</table>
EOT;
	}

	protected function render_head()
	{
		$html = '';

		foreach ($this->columns as $column)
		{
			$th = new Element('th');
			$th[Element::INNER_HTML] = '<div>' . $column->render_header($th) . '</div>';

			$html .= $th;
		}

		return <<<EOT
<thead>
	$html
</thead>
EOT;
	}

	protected function render_foot()
	{

	}

	/**
	 * Renders body.
	 *
	 * @return Element An {@link Element} instance representing a `tbody` element. Its children
	 * are the rendered rows returned by {@link render_rows()}.
	 */
	protected function render_body()
	{
		$rendered_cells = $this->render_cells($this->columns);
		$rendered_cells = $this->alter_rendered_cells($rendered_cells);
		$rows = $this->columns_to_rows($rendered_cells);
		$rendered_rows = $this->render_rows($rows);

		return new Element('tbody', [ Element::CHILDREN => $rendered_rows ]);
	}

	/**
	 * Renders the cells of the columns.
	 *
	 * The method returns an array with the following layout:
	 *
	 *     [<column_id>][] => <cell_content>
	 *
	 * @param array $columns The columns to render.
	 *
	 * @return array[string]mixed
	 */
	protected function render_cells(array $columns)
	{
		$rendered_cells = [];

		foreach ($columns as $id => $column)
		{
			foreach ($this->entries as $entry)
			{
				try
				{
					$content = (string) $column->render_cell($entry);
				}
				catch (\Exception $e)
				{
					$content = render_exception($e);
				}

				$rendered_cells[$id][] = $content;
			}
		}

		return $rendered_cells;
	}

	/**
	 * Alters the rendering cells.
	 *
	 * Note: The method returns the rendered cells as is.
	 *
	 * @param array $rendered_cells
	 *
	 * @return array[string]mixed
	 */
	protected function alter_rendered_cells(array $rendered_cells)
	{
		return $rendered_cells;
	}

	/**
	 * Convert rendered cells to rows.
	 *
	 * @param array $rendered_cells
	 *
	 * @return array[]array
	 */
	protected function columns_to_rows(array $rendered_cells)
	{
		$rows = array();

		foreach ($rendered_cells as $column_id => $cells)
		{
			foreach ($cells as $i => $cell)
			{
				$rows[$i][$column_id] = $cell;
			}
		}

		return $rows;
	}

	/**
	 * Renders the specified rows.
	 *
	 * The rows are rendered as an array of {@link Element} instances representing `TR` elements.
	 *
	 * @param array $rows
	 *
	 * @return array[]Element
	 */
	protected function render_rows(array $rows)
	{
		$columns = $this->columns;
		$rendered_rows = [];

		foreach ($rows as $i => $cells)
		{
			$html = '';

			foreach ($cells as $column_id => $cell)
			{
				$cell = $cell ?: '&nbsp;';
				$class = trim('cell--' . normalize($column_id) . ' ' . $columns[$column_id]->class);

				$html .= <<<EOT
<td class="{$class}">$cell</td>
EOT;
			}

			$rendered_rows[] = new Element('tr', [ Element::INNER_HTML => $html ]);
		}

		return $rendered_rows;
	}
}

/**
 * Representation of a listview column.
 */
class ListViewColumn extends \ICanBoogie\Object
{
	protected $id;
	protected $options;
	protected $listview;

	public function __construct(ListView $listview, $id, array $options=[])
	{
		$this->id = $id;
		$this->listview = $listview;
		$this->options = $options + [

			'class' => null,
			'title' => null

		];
	}

	protected function get_class()
	{
		return $this->options['class'];
	}

	public function render_cell($entry)
	{
		return $entry->{ $this->id };
	}

	public function render_header(Element $container)
	{
		$container['class'] .= 'header--' . normalize($this->id) . ' ' . $this->class;

		return $this->options['title'];
	}
}
