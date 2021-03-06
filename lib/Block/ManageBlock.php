<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block;

use Brickrouge\A;
use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\SchemaColumn;
use ICanBoogie\Facets\QueryString;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use Brickrouge\Alert;
use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\ListView;
use Brickrouge\Ranger;

use Icybee\Binding\Core\PrototypedBindings;
use Icybee\Block\ManageBlock\CriterionColumn;
use Icybee\Block\ManageBlock\SearchElement;
use Icybee\Block\ManageBlock\Column;
use Icybee\Block\ManageBlock\Options;
use Icybee\Block\ManageBlock\Translator;
use Icybee\Element\ActionBarContexts;
use Icybee\Element\ActionBarOperations;
use Icybee\Element\ActionBarSearch;

/**
 * Manages the records of a module.
 *
 * @property-read \ICanBoogie\EventCollection $events
 * @property-read \ICanBoogie\HTTP\Request $request
 * @property-read \Icybee\Modules\Users\User $user
 *
 * @property-read \ICanBoogie\ActiveRecord\Model|\ICanBoogie\Binding\Facets\ModelBindings $model
 * @property-read string $primary_key The primary key of the records.
 * @property-read Options $options The display options.
 * @property-read bool $is_filtering `true` if records are filtered.
 * @property-read Translator $t The translator used by the element.
 *
 * @TODO-20130626:
 *
 * - [filters][options] -> [filter_options]
 */
class ManageBlock extends ListView
{
	use PrototypedBindings;

	const DISCREET_PLACEHOLDER = '<span class="lighter">―</span>';

	const T_BLOCK = '#manager-block';
	const T_COLUMNS_ORDER = '#manager-columns-order';
	const T_ORDER_BY = '#manager-order-by';

	#
	# sort constants
	#

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->js->add(__DIR__ . '/ManageBlock.js', -170);
		$document->css->add(\Icybee\ASSETS . 'css/manage.css', -170);
	}

	/**
	 * Currently used module.
	 *
	 * @var Module
	 */
	public $module;

	/**
	 * Currently used model.
	 *
	 * @var ActiveRecord\Model
	 */
	protected $model;

	/**
	 * Returns the {@link $model} property.
	 *
	 * @return ActiveRecord\Model
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
	 * @return \ICanBoogie\EventCollection
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
		$this->module = $module;
		$this->model = $module->model;

		parent::__construct($attributes + [

			Element::TRANSLATOR => new Translator($module),

			'class' => 'listview listview-interactive'

		]);

		$this->columns;
		$this->jobs = $this->get_jobs();
	}

	/**
	 * Returns the available columns.
	 *
	 * @return array
	 */
	protected function get_available_columns()
	{
		$primary_key = $this->model->primary;

		if ($primary_key)
		{
			return [ $primary_key => ManageBlock\KeyColumn::class ];
		}

		return [];
	}

	protected function lazy_get_columns()
	{
		$columns = $this->get_available_columns();

		new ManageBlock\RegisterColumnsEvent($this, $columns);

		$columns = $this->resolve_columns($columns);

		new ManageBlock\AlterColumnsEvent($this, $columns);

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

		return parent::resolve_columns($columns);
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
	 * @param array $conditions
	 * @param array $modifiers
	 *
	 * @return array Updated filters.
	 */
	protected function update_filters(array $conditions, array $modifiers)
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
				unset($conditions[$identifier]);

				continue;
			}

			$conditions[$identifier] = $value;
		}

		/* @var $column Column */

		foreach ($this->columns as $id => $column)
		{
			$column->alter_conditions($conditions, $modifiers);
		}

		return $conditions;
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

		if (!empty($modifiers['q']))
		{
			$this->alter_modifiers_with_query_string($modifiers, $modifiers['q']);
		}

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
				$this->alter_records($records);
				$this->records = array_values($records);
			}

			#

			$this->events->attach(function(ActionBarSearch\AlterInnerHTMLEvent $event, ActionBarSearch $target) {

				$event->html .= $this->render_search();

			});

			#

			$this->events->attach(function(ActionBarContexts\CollectItemsEvent $event, ActionBarContexts $target) {

				$event->items[] = $this->render_jobs($this->jobs);

			});
		}
		catch (\Exception $e)
		{
			$options->order_by = null;
			$options->order_direction = null;
			$options->filters = [];
			$options->store();

			$rendered_exception = \Brickrouge\render_exception($e);

			return <<<EOT
<div class="alert alert-danger">
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
	<form id="manager" method="GET">
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
	 * @return Query
	 */
	protected function resolve_query(Options $options)
	{
		$query = new Query($this->model);
		$query = $this->alter_query($query, $options->filters);

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
	 * Alters modifiers with a query string.
	 *
	 * @param array $modifiers
	 * @param string $query_string
	 *
	 * @return $this
	 */
	protected function alter_modifiers_with_query_string(array &$modifiers, $query_string)
	{
		$q = new QueryString($query_string);

		foreach ($this->columns as $column)
		{
			if (!$column instanceof CriterionColumn)
			{
				continue;
			}

			$column->criterion->parse_query_string($q);
		}

		$modifiers = array_merge($modifiers, $q->conditions);
		$modifiers['q'] = $q->remains;

		return $this;
	}

	/**
	 * Alters the initial query with the specified filters.
	 *
	 * The `alter_query` method of each column is invoked in turn to alter the query.
	 * The `alter_query_with_value` method of each column is invoked in turn to alter the query.
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

			$query = $column->alter_query_with_value($query, $filters[$id]);
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
	 * @return ActiveRecord[]
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
	 * @param ActiveRecord[] $records
	 *
	 * @return array
	 */
	protected function alter_records(array &$records)
	{
		foreach ($this->columns as $column)
		{
			$column->alter_records($records);
		}
	}

	/**
	 * Decorates a column header content.
	 *
	 * @inheritdoc
	 */
	protected function decorate_header($content, $column_id)
	{
		$column = $this->columns[$column_id];
		$header_options = $column->render_options();
		$element = parent::decorate_header("<div>{$content}{$header_options}</div>", $column_id);

		if ($this->count > 1 || $this->options->filters || $this->options->search)
		{
			if ($column->orderable)
			{
				$element->add_class('orderable');
			}

			if ($column->is_filtering)
			{
				$element->add_class('filtering');
			}

			if ($column->filters)
			{
				$element->add_class('filters');
			}
		}

		if ($header_options)
		{
			$element->add_class('has-options');
		}

		if (!$content)
		{
			$element->add_class('has-no-label');
		}

		return $element;
	}

	/**
	 * Replaces repeating values of a column with the discreet placeholder.
	 *
	 * @param array $rendered_cells
	 *
	 * @return array
	 */
	protected function apply_discreet_filter(array $rendered_cells)
	{
		$discreet_column_cells = $rendered_cells;
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
	 * @inheritdoc
	 */
	protected function alter_cells(array &$rendered_cells)
	{
		new ManageBlock\AlterRenderedCellsEvent($this, $rendered_cells, $this->records);

		$rendered_cells = $this->apply_discreet_filter($rendered_cells);
	}

	/**
	 * Adds the class `no-ownership` on rows representing records that the user does not own.
	 *
	 * @inheritdoc
	 */
	protected function alter_rows(array &$rendered_rows)
	{
		if (!$this->primary_key)
		{
			return;
		}

		$user = $this->user;
		$records = $this->records;

		/* @var $row Element */

		foreach ($rendered_rows as $i => $row)
		{
			if ($user->has_ownership($records[$i]))
			{
				continue;
			}

			$row->add_class('no-ownership');
		}
	}

	/**
	 * Renders an alternate body when there is no record to display.
	 *
	 * @return Alert
	 */
	protected function render_no_records()
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
			$url = $this->app->url_for("admin:{$this->module->id}:new");

			$message = new A($this->t('create_first'), $url, [

				'class' => 'alert-link'

			]);

			$context = 'info';
		}

		return new Alert($message, [

			Alert::CONTEXT => $context,

			'class' => 'alert listview-alert'

		]);
	}

	/**
	 * Renders the "search" element to be injected in the document.
	 *
	 * @return SearchElement
	 */
	protected function render_search()
	{
		return new SearchElement([

			'title' => $this->t('Search in the records'),
			'placeholder' => $this->t('Search'),
			'value' => $this->options->search

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
			$content = $this->t($this->is_filtering || $this->options->search ? "records_count_with_filters" : "records_count", [ ':count' => $count ]);

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
				'value' => $limit,
				'class' => 'form-control form-control-inline'

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
	 * @return Element
	 */
	protected function render_jobs(array $jobs)
	{
		return new ActionBarOperations([ Element::OPTIONS => $jobs ]);
	}

	/**
	 * Renders the element's footer.
	 *
	 * @return string
	 */
	protected function render_foot()
	{
		$n_columns = count($this->columns);
		$key_column = $this->primary_key ? '<td class="key">&nbsp;</td>' : '';

		if ($key_column)
		{
			$n_columns--;
		}

		$rendered_controls = $this->render_controls();

		return <<<EOT
<tfoot>
	<tr>
		$key_column
		<td colspan="{$n_columns}">{$rendered_controls}</td>
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
	public function is_filtering($column_id = null)
	{
		return $this->options->is_filtering($column_id);
	}

	protected function get_is_filtering()
	{
		return $this->is_filtering();
	}
}
