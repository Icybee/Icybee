<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge\Alert;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Ranger;
use Brickrouge\Text;

use Icybee\Element\ActionbarContexts;
use Icybee\Element\ActionbarSearch;
use Icybee\ManageBlock\Column;
use Icybee\ManageBlock\Options;
use Icybee\ManageBlock\Translator;

/* @var $column \Icybee\ManageBlock\Column */

/**
 * An element to manage the records of a module.
 *
 * @property-read \ICanBoogie\Core $app
 * @property-read \ICanBoogie\EventCollection $events
 * @property-read \ICanBoogie\HTTP\Request $request
 * @property-read \Icybee\Modules\Users\User $user
 *
 * @property-read \ICanBoogie\ActiveRecord\Model $model
 * @property-read string $primary_key The primary key of the records.
 * @property-read Options $options The display options.
 * @property-read bool $is_filtering `true` if records are filtered.
 * @property-read Translator $t The translator used by the element.
 *
 * @changes-20130622
 *
 * - All extend_column* methods are removed.
 * - alter_range_query() signature changed, $options is now an instance of Options an not an array.
 * - AlterColumnsEvent has been redesigned, `records` is removed.
 *
 * @TODO-20130626:
 *
 * - [filters][options] -> [filter_options]
 * - throw error when COLUMNS_ORDER use an undefined column.
 */
class ManageBlock extends Element
{
	const DISCREET_PLACEHOLDER = '<span class="lighter">―</span>';

	const T_BLOCK = '#manager-block';
	const T_COLUMNS_ORDER = '#manager-columns-order';
	const T_ORDER_BY = '#manager-order-by';

	#
	# sort constants
	#

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('manage.js', -170);
		$document->css->add(\Icybee\ASSETS . 'css/manage.css', -170);
	}

	/**
	 * Currently used module.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Currently used model.
	 *
	 * @var \ICanBoogie\ActiveRecord\Model
	 */
	protected $model;

	/**
	 * Returns the {@link $model} property.
	 *
	 * @return \ICanBoogie\ActiveRecord\Model
	 */
	protected function get_model()
	{
		return $this->model;
	}

	/**
	 * The columns of the element.
	 *
	 * @var Column[]
	 */
	protected $columns;

	/**
	 * The records to display.
	 *
	 * @var ActiveRecord[]
	 */
	protected $records;

	/**
	 * The total number of records matching the filters.
	 *
	 * @var int
	 */
	protected $count;

	/**
	 * Returns the primary key of the records.
	 *
	 * @return string
	 */
	protected function get_primary_key()
	{
		return $this->model->primary;
	}

	/**
	 * Jobs that can be applied to the records.
	 *
	 * @var array
	 */
	protected $jobs = [];

	protected $browse;

	/**
	 * Returns the {@link $t} property.
	 *
	 * @return \ICanBoogie\I18n\Translator\Proxi
	 */
	protected function get_t()
	{
		return $this[self::TRANSLATOR];
	}

	/**
	 * Display options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Returns the {@link $options} property.
	 *
	 * @return Options
	 */
	protected function get_options()
	{
		return $this->options;
	}

	/**
	 * Returns application's events
	 *
	 * @return \ICanBoogie\Events
	 */
	protected function get_events()
	{
		return $this->app->events;
	}

	/**
	 * Returns application's request.
	 *
	 * @return \ICanBoogie\HTTP\Request
	 */
	protected function get_request()
	{
		return $this->app->request;
	}

	/**
	 * Returns application's user.
	 *
	 * @return \Icybee\Modules\Users\User
	 */
	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct(Module $module, array $attributes)
	{
		## 20130625: checking deprecated methods

		if (method_exists($this, 'get_query_conditions'))
		{
			throw new \Exception("The <q>get_query_conditions()</q> method is deprecated. Use <q>alter_query()</q> instead.");
		}

		if (method_exists($this, 'extend_column'))
		{
			throw new \Exception("The <q>extend_column()</q> method is deprecated. Define columns with classes.");
		}

		if (method_exists($this, 'extend_columns'))
		{
			throw new \Exception("The <q>extend_columns()</q> method is deprecated. Define columns with classes.");
		}

		if (method_exists($this, 'retrieve_options'))
		{
			throw new \Exception("The <q>retrieve_options()</q> method is deprecated. Use <q>resolve_options()</q>.");
		}

		if (method_exists($this, 'store_options'))
		{
			throw new \Exception("The <q>store_options()</q> method is deprecated. Use the Options instance.");
		}

		if (method_exists($this, 'alter_range_query'))
		{
			throw new \Exception("The <q>alter_range_query()</q> method is deprecated. Use columns and <q>alter_query_with_limit()</q>.");
		}

		if (method_exists($this, 'load_range'))
		{
			throw new \Exception("The <q>load_range()</q> method is deprecated. Use <q>fetch_records()</q>.");
		}

		if (method_exists($this, 'parseColumns'))
		{
			throw new \Exception("The <q>parseColumns()</q> method is deprecated. Use <q>resolve_columns()</q>.");
		}

		if (method_exists($this, 'columns'))
		{
			throw new \Exception("The <q>columns()</q> method is deprecated. Use <q>get_available_columns()</q>.");
		}

		if (method_exists($this, 'jobs'))
		{
			throw new \Exception("The <q>jobs()</q> method is deprecated. Use <q>get_available_jobs()</q>.");
		}

		if (method_exists($this, 'addJob'))
		{
			throw new \Exception("The <q>addJob()</q> method is deprecated. Use <q>resolve_jobs()</q>.");
		}

		if (method_exists($this, 'getJobs'))
		{
			throw new \Exception("The <q>getJobs()</q> method is deprecated. Use <q>render_jobs()</q>.");
		}

		if (method_exists($this, 'render_limiter'))
		{
			throw new \Exception("The <q>render_limiter()</q> method is deprecated. Use <q>render_controls()</q>.");
		}

		$class_reflection = new \ReflectionClass($this);

		foreach ($class_reflection->getMethods() as $method_reflection)
		{
			if (strpos($method_reflection->name, 'extend_column_') === 0)
			{
				throw new \Exception("The <q>{$method_reflection->name}</q> method is deprecated. Use a column class.");
			}

			if (strpos($method_reflection->name, 'render_column_') === 0)
			{
				throw new \Exception("The <q>{$method_reflection->name}</q> method is deprecated. Use a column class.");
			}

			if (strpos($method_reflection->name, 'render_cell_') === 0)
			{
				throw new \Exception("The <q>{$method_reflection->name}</q> method is deprecated. Use a column class.");
			}
		}

		## /20130625

		parent::__construct('div', $attributes + [

			Element::TRANSLATOR => new Translator($module),

			'class' => 'listview listview-interactive'

		]);

		$this->module = $module;
		$this->model = $module->model;
		$this->columns = $this->get_columns();
		$this->jobs = $this->get_jobs();
	}

	/**
	 * Returns the available columns.
	 *
	 * @return array[string]mixed
	 */
	protected function get_available_columns()
	{
		$primary_key = $this->model->primary;

		if ($primary_key)
		{
			return [ $primary_key => 'Icybee\ManageBlock\KeyColumn' ];
		}

		return [];
	}

	protected function get_columns()
	{
		$columns = $this->get_available_columns();

		new \Icybee\ManageBlock\RegisterColumnsEvent($this, $columns);

		$columns = $this->resolve_columns($columns);

		new \Icybee\ManageBlock\AlterColumnsEvent($this, $columns);

		foreach ($columns as $column_id => $column)
		{
			if ($column instanceof Column)
			{
				continue;
			}

			throw new \UnexpectedValueException(\ICanBoogie\format('Column %id must be an instance of Column. Given: %type. :data', [

				'%id' => $column_id,
				'%type' => gettype($column),
				':data' => $column

			]));
		}

		return $columns;
	}

	protected function resolve_columns(array $columns)
	{
		$columns_order = $this[self::T_COLUMNS_ORDER];

		if ($columns_order)
		{
			$primary = $this->model->primary;

			if ($primary)
			{
				array_unshift($columns_order, $primary);
			}

			$columns_order = array_combine($columns_order, array_fill(0, count($columns_order), null));
			$columns = array_intersect_key($columns, $columns_order);
			$columns = array_merge($columns_order, $columns);
		}

		$resolved_columns = [];

		foreach ($columns as $id => $options)
		{
			if ($options === null)
			{
				throw new \Exception(\ICanBoogie\format("Column %id is not defined.", [ 'id' => $id ]));
			}

			$construct = __CLASS__ . '\Column';

			if (is_string($options))
			{
				$construct = $options;
				$options = [];
			}

			$resolved_columns[$id] = new $construct($this, $id, $options);
		}

		return $resolved_columns;
	}

	/**
	 * Returns the available jobs.
	 *
	 * @return array[string]mixed
	 */
	protected function get_available_jobs()
	{
		return [];
	}

	/**
	 * Returns the jobs.
	 *
	 * @return array[string]mixed
	 */
	protected function get_jobs()
	{
		$jobs = $this->get_available_jobs();
		$jobs = $this->resolve_jobs($jobs);

		return $jobs;
	}

	/**
	 * Resolves the available jobs.
	 *
	 * @param array $jobs
	 *
	 * @return array
	 */
	protected function resolve_jobs(array $jobs)
	{
		if ($this->primary_key)
		{
			$jobs = array_merge([ Module::OPERATION_DELETE => $this->t('delete.operation.short_title') ], $jobs);
		}

		return $jobs;
	}

	/**
	 * Update filters with the specified modifiers.
	 *
	 * The extended schema of the model is used to automatically handle booleans, integers,
	 * dates (date, datetime and timestamp) and strings (char, varchar).
	 *
	 * @param array $filters
	 * @param array $modifiers
	 *
	 * @return array Updated filters.
	 */
	protected function update_filters(array $filters, array $modifiers)
	{
		static $as_strings = [ 'char', 'varchar', 'date', 'datetime', 'timestamp' ];

		$schema = $this->model->extended_schema;

		foreach ($modifiers as $identifier => $value)
		{
			if (empty($schema[$identifier]))
			{
				continue;
			}

			$type = $schema[$identifier]->type;

			if ($type == SchemaColumn::TYPE_BOOLEAN)
			{
				$value = $value === '' ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			}
			else if ($type == SchemaColumn::TYPE_INTEGER)
			{
				$value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
			}
			else if (in_array($type, $as_strings))
			{
				if ($value === '')
				{
					$value = null;
				}
			}
			else continue;

			if ($value === null)
			{
				unset($filters[$identifier]);

				continue;
			}

			$filters[$identifier] = $value;
		}

		/* @var $column Column */

		foreach ($this->columns as $id => $column)
		{
			$filters = $column->alter_filters($filters, $modifiers);
		}

		return $filters;
	}

	/**
	 * Updates options with the provided modifiers.
	 *
	 * The method updates the `order`, `start`, `limit`, `search` and `filters` options.
	 *
	 * The `start` options is reset to 1 when the `order`, `search` or `filters` options change.
	 *
	 * @param Options $options Previous options.
	 * @param array $modifiers Options modifiers.
	 *
	 * @return Options Updated options.
	 */
	protected function update_options(Options $options, array $modifiers)
	{
		$modifiers['filters'] = $this->update_filters($options->filters, $modifiers);

		return $options->update($modifiers);
	}

	/**
	 * Resolves the display order of the records according to the default options and the
	 * available columns.
	 *
	 * If the column that should be used to order the records does not exists, the order is
	 * reseted.
	 *
	 * If the order direction if not defined, the default direction of the column if used
	 * instead.
	 *
	 * @param string|null $order_by The identifier of the column used to order the records.
	 * @param string|int|null $order_direction The direction of the ordering. One of: "asc",
	 * "desc", 1, -1 or `null`.
	 *
	 * @return array Returns an array made of the column identifier and the order direction.
	 */
	protected function resolve_order($order_by, $order_direction)
	{
		$columns = $this->columns;
		$default_order = $this[self::T_ORDER_BY];

		if (!$order_by && $default_order)
		{
			list($order_by, $order_direction) = (array) $default_order + [ 1 => 'desc' ];

			$order_direction = ($order_direction == 'desc') ? -1 : 1;
		}

		if ($order_by && empty($columns[$order_by]))
		{
			\ICanBoogie\log_error("Undefined column for order: !order.", [ 'order' => $order_by ]);

			$order_by = null;
			$order_direction = null;
		}

		if (!$order_direction && isset($columns[$order_by]))
		{
			$order_direction = $columns[$order_by]->default_order;
		}

		return [ $order_by, $order_direction ];
	}

	/**
	 * Returns the options for the element.
	 *
	 * Options are restored from the storing backend and updated according to the supplied
	 * modifiers.
	 *
	 * @param string $name
	 * @param array $modifiers
	 *
	 * @return Options
	 */
	protected function resolve_options($name, array $modifiers)
	{
		$options = new Options($name);
		$options->retrieve();
		$options = $this->update_options($options, $modifiers);

		list($order_by, $order_direction) = $this->resolve_order($options->order_by, $options->order_direction);

		$options->order_by = $order_by;
		$options->order_direction = $order_direction;
		$options->store();

		return $options;
	}

	/**
	 * Renders the element.
	 *
	 * If an error occurred while creating the query or fetching the records, the filters and the
	 * order are reset.
	 */
	public function render()
	{
		$options = $this->options = $this->resolve_options($this->module->flat_id, $this->request->params);
		$order_by = $options->order_by;

		if ($order_by)
		{
			$order_column = $this->columns[$order_by];
			$order_column->order = $options->order_direction;
		}

		try
		{
			$query = $this->resolve_query($options);
			$records = $this->fetch_records($query);

			if ($records)
			{
				$records = $this->alter_records($records);
				$this->records = array_values($records);
			}
		}
		catch (\Exception $e)
		{
			$options->order_by = null;
			$options->order_direction = null;
			$options->filters = array();
			$options->store();

			$rendered_exception = \Brickrouge\render_exception($e);

			return <<<EOT
<div class="alert alert-error alert-block undismissable">
	<p>There was an error in the SQL statement, orders and filters have been reset,
	please reload the page.</p>

	$rendered_exception
</div>
EOT;
		}

		$html = parent::render();
		$document = \Brickrouge\get_document();

		foreach ($this->columns as $column)
		{
			$column->add_assets($document);
		}

		return $html;
	}

	/**
	 * Renders the object into a HTML string.
	 */
	protected function render_inner_html()
	{
		$records = $this->records;
		$options = $this->options;

		if ($records || $options->filters)
		{
			if ($records)
			{
				$body = '<tbody>' . $this->render_body() . '</tbody>';
			}
			else
			{
				$body = '<tbody class="empty"><tr><td colspan="' . count($this->columns) . '">' . $this->render_empty_body() . '</td></tr></tbody>';
			}

			$head = $this->render_head();
			$foot = $this->render_foot();

			$content = <<<EOT
<table>
	$head
	$foot
	$body
</table>
EOT;
		}
		else
		{
			$body = $this->render_empty_body();
			$foot = $this->render_foot();
			$columns_n = count($this->columns);

			$content = <<<EOT
<table>
	<tbody class="empty" td colspan="$columns_n">$body</tbody>
	$foot
</table>
EOT;
		}

		#

		$search = $this->render_search();

		$this->events->attach(function(ActionbarSearch\AlterInnerHTMLEvent $event, ActionbarSearch $sender) use($search)
		{
			$event->html .= $search;
		});

		#

		$rendered_jobs = $this->render_jobs($this->jobs);

		$this->events->attach(function(ActionbarContexts\CollectItemsEvent $event, ActionbarContexts $target) use($rendered_jobs) {

			$event->items[] = $rendered_jobs;

		});

		#

		return $content;
	}

	/**
	 * Wraps the listview in a `form` element.
	 */
	protected function render_outer_html()
	{
		$html = parent::render_outer_html();

		$operation_name = Operation::DESTINATION;
		$operation_value = $this->module->id;

		$block_name = self::T_BLOCK;
		$block_value = $this[self::T_BLOCK] ?: 'manage';

		return <<<EOT
<div brickrouge-is="ManageBlock">
	<form id="manager" method="GET" action="">
		<input type="hidden" name="{$operation_name}" value="{$operation_value}" />
		<input type="hidden" name="{$block_name}" value="{$block_value}" />
		$html
	</form>
</div>
EOT;
	}

	/**
	 * Resolve ActiveRecord query according to the supplied options.
	 *
	 * Note: The method updates the {@link $count} property with the number of records matching
	 * the query, before a range is applied.
	 *
	 * @param Options $options
	 *
	 * @return \ICanBoogie\ActiveRecord\Query
	 */
	protected function resolve_query(Options $options)
	{
		$query = new Query($this->model);
		$query = $this->alter_query($query, $options->filters);

		#

		new ManageBlock\AlterQueryEvent($this, $query, $options);

		#

		$search = $options->search;

		if ($search)
		{
			$query = $this->alter_query_with_search($query, $search);
		}

		#
		# Adjust `start` so that it's never greater than `count`.
		#

		$start = $options->start;
		$count = $this->count = $query->count;

		if ($start > $count)
		{
			$options->start = 1;
			$options->store();
		}
		else if ($start < -$count)
		{
			$options->start = 1;
			$options->store();
		}
		else if ($start < 0)
		{
			$start = -(-($start - 1) % $count) + $count;
			$start = ceil($start / $options->limit) * $options->limit + 1;

			$options->start = $start;
			$options->store();
		}

		$order_by = $options->order_by;

		if ($order_by)
		{
			$query = $this->columns[$order_by]->alter_query_with_order($query, $options->order_direction);
		}

		return $this->alter_query_with_range($query, $options->start - 1, $options->limit);
	}

	/**
	 * Alters the initial query with the specified filters.
	 *
	 * The `alter_query` method of each column is invoked in turn to alter the query.
	 * The `alter_query_with_filter` method of each column is invoked in turn to alter the query.
	 *
	 * @param Query $query
	 * @param array $filters
	 *
	 * @return Query The altered query.
	 */
	protected function alter_query(Query $query, array $filters)
	{
		foreach ($this->columns as $column)
		{
			$query = $column->alter_query($query);
		}

		foreach ($this->columns as $id => $column)
		{
			if (!isset($filters[$id]))
			{
				continue;
			}

			$query = $column->alter_query_with_filter($query, $filters[$id]);
		}

		return $query;
	}

	/**
	 * Alters the query according to a search string.
	 *
	 * @param Query $query
	 * @param string $search
	 *
	 * @return Query
	 */
	protected function alter_query_with_search(Query $query, $search)
	{
		static $supported_types = [ 'char', 'varchar', 'text' ];

		$words = explode(' ', $search);
		$words = array_map('trim', $words);

		$schema = $this->model->extended_schema;

		foreach ($words as $word)
		{
			$concats = '';

			foreach ($schema as $identifier => $column)
			{
				$type = $column->type;

				if (!in_array($type, $supported_types))
				{
					continue;
				}

				if ($column->null)
				{
					$identifier = "IFNULL(`$identifier`, \"\")";
				}

				$concats .= ', `' . $identifier . '`';
			}

			if (!$concats)
			{
				continue;
			}

			$query->where('CONCAT_WS(" ", ' . substr($concats, 2) . ') LIKE ?', "%{$word}%");
		}

		return $query;
	}

	/**
	 * Alters query with range (offset and limit).
	 *
	 * @param Query $query
	 * @param int $offset The offset of the record to return.
	 * @param int $limit The maximum number of records to return.
	 *
	 * @return Query
	 */
	protected function alter_query_with_range(Query $query, $offset, $limit)
	{
		return $query->limit($offset, $limit);
	}

	/**
	 * Fetches the records matching the query.
	 *
	 * @param Query $query
	 *
	 * @return \ICanBoogie\ActiveRecord[]
	 */
	protected function fetch_records(Query $query)
	{
		return $query->all;
	}

	/**
	 * Alters records.
	 *
	 * The function return the records _as is_ but subclasses can implement the method to
	 * load all the dependencies of the records in a single step.
	 *
	 * @param array $records
	 *
	 * @return array
	 */
	protected function alter_records(array $records)
	{
		foreach ($this->columns as $column)
		{
			$records = $column->alter_records($records);
		}

		return $records;
	}

	/**
	 * Renders the THEAD element.
	 *
	 * @return string The rendered THEAD element.
	 */
	protected function render_head()
	{
		$cells = '';

		foreach ($this->columns as $id => $column)
		{
			$cells .= $this->render_column($column, $id);
		}

		return <<<EOT
<thead>
	<tr>$cells</tr>
</thead>
EOT;
	}

	/**
	 * Renders a column header.
	 *
	 * @param Column $column
	 * @param string $id
	 *
	 * @return string The rendered THEAD cell.
	 */
	protected function render_column(Column $column, $id)
	{
		$class = 'header--' . \Brickrouge\normalize($id) . ' ' . $column->class;

		if ($this->count > 1 || $this->options->filters || $this->options->search)
		{
			$orderable = $column->orderable;

			if ($orderable)
			{
				$class .= ' orderable';
			}

			$filtering = $column->is_filtering;

			if ($filtering)
			{
				$class .= ' filtering';
			}

			$filters = $column->filters;

			if ($filters)
			{
				$class .= ' filters';
			}
		}

		$header_options = $column->render_options();

		if ($header_options)
		{
			$class .= ' has-options';
		}

		$header = $column->render_header();

		if (!$header)
		{
			$class .= ' has-no-label';
		}

		$class = trim($class);

		return <<<EOT
<th class="$class"><div>{$header}{$header_options}</div></th>
EOT;
	}

	/**
	 * Renders the cells of the columns.
	 *
	 * The method returns an array with the following layout:
	 *
	 *     [<column_id>][] => <cell_content>
	 *
	 * @param Column[] $columns The columns to render.
	 *
	 * @return array
	 */
	protected function render_columns_cells(array $columns)
	{
		$rendered_columns_cells = array();

		foreach ($columns as $id => $column)
		{
			foreach ($this->records as $record)
			{
				try
				{
					$content = (string) $column->render_cell($record);
				}
				catch (\Exception $e)
				{
					$content = \Brickrouge\render_exception($e);
				}

				$rendered_columns_cells[$id][] = $content;
			}
		}

		return $rendered_columns_cells;
	}

	/**
	 * Replaces repeating values of a column with the discreet placeholder.
	 *
	 * @param array $rendered_columns_cells
	 *
	 * @return array[string]mixed
	 */
	protected function apply_discreet_filter(array $rendered_columns_cells)
	{
		$discreet_column_cells = $rendered_columns_cells;
		$columns = $this->columns;

		foreach ($discreet_column_cells as $id => &$cells)
		{
			$column = $columns[$id];

			if (!$column->discreet)
			{
				continue;
			}

			$last_content = null;

			foreach ($cells as &$content)
			{
				if ($last_content !== $content || !$content)
				{
					$last_content = $content;

					continue;
				}

				$content = self::DISCREET_PLACEHOLDER;
			}
		}

		return $discreet_column_cells;
	}

	/**
	 * Convert rendered columns cells to rows.
	 *
	 * @param array $rendered_columns_cells
	 *
	 * @return array[]array
	 */
	protected function columns_to_rows(array $rendered_columns_cells)
	{
		$rows = [];

		foreach ($rendered_columns_cells as $column_id => $cells)
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
	 * @return Element[]
	 */
	protected function render_rows(array $rows)
	{
		$rendered_rows = [];
		$columns = $this->columns;
		$records = $this->records;
		$key = $this->primary_key;
		$module = $this->module;
		$user = $this->user;

		foreach ($rows as $i => $cells)
		{
			$html = '';

			foreach ($cells as $column_id => $cell)
			{
				$html .= '<td class="'
				. trim('cell--' . \Brickrouge\normalize($column_id) . ' ' . $columns[$column_id]->class)
				. '">' . ($cell ?: '&nbsp;') . '</td>';
			}

			$tr = new Element('tr', [ Element::INNER_HTML => $html ]);

			if ($key && !$user->has_ownership($module, $records[$i]))
			{
				$tr->add_class('no-ownership');
			}

			$rendered_rows[] = $tr;
		}

		return $rendered_rows;
	}

	/**
	 * Renders table body.
	 *
	 * @return string
	 */
	protected function render_body()
	{
		$rendered_cells = $this->render_columns_cells($this->columns);

		new ManageBlock\AlterRenderedCellsEvent($this, $rendered_cells, $this->records);

		$rendered_cells = $this->apply_discreet_filter($rendered_cells);
		$rows = $this->columns_to_rows($rendered_cells);
		$rendered_rows = $this->render_rows($rows);

		return implode(PHP_EOL, $rendered_rows);
	}

	/**
	 * Renders an alternate body when there is no record to display.
	 *
	 * @return \Brickrouge\Alert
	 */
	protected function render_empty_body()
	{
		$search = $this->options->search;
		$filters = $this->options->filters;
		$context = null;

		if ($search)
		{
			$message = $this->t("Your search %search did not match any record.", [ 'search' => $search ]);
			$btn_label = $this->t("Reset search filter");

			$message = <<<EOT
$message
<br /><br />
<a href="?q=" rel="manager/search" data-action="reset" class="btn btn-warning">$btn_label</a>
EOT;
		}
		else if ($filters)
		{
			// TODO-20130629: column should implement a humanize_filter() method that would return a humanized filter expression.

			$filters = implode(', ', $filters);
			$message = $this->t('Your selection %selection dit not match any record.', [ 'selection' => $filters ]);
		}
		else
		{
			$message = $this->t('create_first', [ '!url' => \ICanBoogie\Routing\contextualize("/admin/{$this->module->id}/new") ]);
			$context = 'info';
		}

		return new Alert($message, [ Alert::CONTEXT => $context, 'class' => 'alert listview-alert' ]);
	}

	/**
	 * Renders the "search" element to be injected in the document.
	 *
	 * @return \Brickrouge\Form
	 */
	protected function render_search()
	{
		$search = $this->options->search;

		return new Element('div', [

			Element::CHILDREN => [

				'q' => new Text([

					'title' => $this->t('Search in the records'),
					'value' => $search,
					'size' => '16',
					'class' => 'search',
					'tabindex' => 0,
					'placeholder' => $this->t('Search')

				]),

				new Button('', [

					'type' => 'button',
					'class' => 'icon-remove'

				])

			],

			'class' => 'listview-search'
		]);
	}

	/**
	 * Renders listview controls.
	 *
	 * @return string
	 */
	protected function render_controls()
	{
		$count = $this->count;
		$start = $this->options->start;
		$limit = $this->options->limit;

		if ($count <= 10)
		{
			$content = $this->t($this->is_filtering || $this->options->search ? "records_count_with_filters" : "records_count", array(':count' => $count));

			return <<<EOT
<div class="listview-controls">
$content
</div>
EOT;
		}

		$ranger = new Ranger('div', [

			Ranger::T_START => $start,
			Ranger::T_LIMIT => $limit,
			Ranger::T_COUNT => $count,
			Ranger::T_EDITABLE => true,
			Ranger::T_NO_ARROWS => true,

			'class' => 'listview-start'

		]);

		$page_limit_selector = null;

		if ($limit >= 20 || $count >= $limit)
		{
			$page_limit_selector = new Element('select', [

				Element::OPTIONS => [ 10 => 10, 20 => 20, 50 => 50, 100 => 100 ],

				'title' => $this->t('Number of item to display by page'),
				'name' => 'limit',
				'value' => $limit

			]);

			$page_limit_selector = '<div class="listview-limit">' . $this->t(':page_limit_selector by page', [ ':page_limit_selector' => (string) $page_limit_selector ]) . '</div>';
		}

		$browse = null;

		if ($count > $limit)
		{
			$browse = <<<EOT
<div class="listview-browse">
	<a href="?start=previous" class="browse previous" rel="manager"><i class="icon-arrow-left"></i></a>
	<a href="?start=next" class="browse next" rel="manager"><i class="icon-arrow-right"></i></a>
</div>
EOT;
		}

		$this->browse = $browse;

		# the hidden select is a trick for vertical alignment with the operation select

		return <<<EOT
<div class="listview-controls">
	{$ranger}{$page_limit_selector}{$browse}
</div>
EOT;
	}

	/**
	 * Renders jobs as an HTML element.
	 *
	 * @param array $jobs
	 *
	 * @return \Brickrouge\Element|null
	 */
	protected function render_jobs(array $jobs)
	{
		if (!$jobs)
		{
			return null;
		}

		$children = [];

		foreach ($jobs as $operation => $label)
		{
			$children[] = new Button($label, [

				'data-operation' => $operation,
				'data-target' => 'manager'

			]);
		}

		return new Element('div', [

			Element::IS => 'ActionBarOperations',

			Element::CHILDREN => [

				new Element('label', [

					Element::INNER_HTML => '',

					'class' => 'btn-group-label count'

				]),

				new Element('div', [

					Element::CHILDREN => $children,

					'class' => 'btn-group'

				]),

				new Button('Annuler la sélection', [ 'data-dismiss' => 'selection' ])
			],

			'data-actionbar-context' => 'operations',
			'data-pattern-one' => "Un élément sélectionné",
			'data-pattern-other' => ":count éléments sélectionnés",

			'class' => 'actionbar-actions listview-operations'

		]);
	}

	/**
	 * Renders the element's footer.
	 *
	 * @return string
	 */
	protected function render_foot()
	{
		$ncolumns = count($this->columns);
		$key_column = $this->primary_key ? '<td class="key">&nbsp;</td>' : '';
		$rendered_jobs = null;
		$rendered_controls = $this->render_controls();

		return <<<EOT
<tfoot>
	<tr>
		$key_column
		<td colspan="{$ncolumns}">{$rendered_jobs}{$rendered_controls}</td>
	</tr>
</tfoot>
EOT;
	}

	/**
	 * Checks if the view is filtered.
	 *
	 * @param string $column_id This optional parameter can be used to check if the filter
	 * is applied to a specific column.
	 *
	 * @return boolean
	 */
	public function is_filtering($column_id=null)
	{
		return $this->options->is_filtering($column_id);
	}

	protected function get_is_filtering()
	{
		return $this->is_filtering();
	}
}

/*
 * Events
 */

namespace Icybee\ManageBlock;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;

use Icybee\ManageBlock;

/**
 * Event class for the `Icybee\ManageBlock::register_columns` event.
 */
class RegisterColumnsEvent extends Event
{
	/**
	 * Reference to the columns of the element.
	 *
	 * @var array[string]array
	 */
	public $columns;

	/**
	 * The event is constructed with the type `register_columns`.
	 *
	 * @param \Icybee\ManageBlock $target
	 * @param array $columns Reference to the columns of the element.
	 */
	public function __construct(ManageBlock $target, array &$columns)
	{
		$this->columns = &$columns;

		parent::__construct($target, 'register_columns');
	}

	public function add(Column $column, $weight=null)
	{
		if ($weight)
		{
			list($position, $relative) = explode(':', $weight) + [ 'before' ];

			$this->columns = \ICanBoogie\array_insert($this->columns, $relative, $column, $column->id, $position == 'after');
		}
		else
		{
			$this->columns[$column->id] = $column;
		}
	}
}

/**
 * Event class for the `Icybee\ManageBlock::alter_columns` event.
 */
class AlterColumnsEvent extends Event
{
	/**
	 * Reference to the columns of the element.
	 *
	 * @var array[string]array
	 */
	public $columns;

	/**
	 * The event is constructed with the type `alter_columns`.
	 *
	 * @param \Icybee\ManageBlock $target
	 * @param array $columns Reference to the columns of the element.
	 */
	public function __construct(ManageBlock $target, array &$columns)
	{
		$this->columns = &$columns;

		parent::__construct($target, 'alter_columns');
	}

	public function add(Column $column, $weight=null)
	{
		if ($weight)
		{
			list($position, $relative) = explode(':', $weight) + [ 'before' ];

			$this->columns = \ICanBoogie\array_insert($this->columns, $relative, $column, $column->id, $position == 'after');
		}
		else
		{
			$this->columns[$column->id] = $column;
		}
	}
}

class AlterRenderedCellsEvent extends Event
{
	/**
	 * Reference to the rendered cells.
	 *
	 * @var array[string]string
	 */
	public $rendered_cells;

	/**
	 * The records used to render the cells.
	 *
	 * @var \ICanBoogie\ActiveRecord[]
	 */
	public $records;

	public function __construct(ManageBlock $target, array &$rendered_cells, array $records)
	{
		$this->rendered_cells = &$rendered_cells;
		$this->records = $records;

		parent::__construct($target, 'alter_rendered_cells');
	}
}

class AlterQueryEvent extends Event
{
	public $query;

	public $options;

	public function __construct(ManageBlock $target, Query $query, Options $options)
	{
		$this->query = $query;
		$this->options = $options;

		parent::__construct($target, 'alter_query');
	}
}
