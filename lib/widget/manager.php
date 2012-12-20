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

use ICanBoogie;
use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Operation;

use Brickrouge;
use Brickrouge\Alert;
use Brickrouge\Button;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Ranger;
use Brickrouge\Text;

use Icybee\Element\ActionbarSearch;

class Manager extends Element
{
	const REPEAT_PLACEHOLDER = '<span class="lighter">―</span>';

	const T_BLOCK = '#manager-block';
	const T_COLUMNS = '#manager-columns';
	const T_COLUMNS_ORDER = '#manager-columns-order';
	const T_KEY = '#manager-key';
	const T_JOBS = '#manager-jobs';
	const T_ORDER_BY = '#manager-order-by';

	#
	# session options constants
	#

	const OPTIONS = 'resume-options';

	#
	# display constants
	#

	const BY = 'by';
	const ORDER = 'order'; // TODO-20100128: remove this and use T_ORDER_BY instead
	const LIMIT = 'limit';
	const START = 'start';

	#
	# column constants
	#

	const COLUMN_HOOK = 'hook';

	#
	# sort constants
	#

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	#
	# variables
	#

	public $module;
	public $model;

	protected $columns;
	protected $entries;
	protected $tags;
	protected $count;

	protected $idtag;
	protected $jobs = array();

	protected $browse;

	/**
	 * Proxis translator with the following scope: "manager.<module_flat_id>"
	 *
	 * @var \ICanBoogie\I18n\Translator\Proxy
	 */
	protected $t;

	/**
	 * @var array The options include:
	 *
	 * int start: The index of the first record to display. 1 for the first record.
	 * int limit: The number of records to display.
	 * array|null order: Columns used to sort the records.
	 * string|null search: Key words to search for, altering the query conditions.
	 * array filters: The filters currently used to filter the records, ready for the
	 * ICanBoogie\ActiveRecord\Query::where() method.
	 */
	protected $options = array();

	#
	# checkboxes count is used to determine wheter or not we should
	# add the 'mastercheckbox'
	#

	protected $checkboxes = 0;

	public function __construct(Module $module, Model $model, array $tags)
	{
		global $core;

		parent::__construct(null, $tags);

		$this->module = $module;
		$this->model = $model;

		if (empty($tags[self::T_COLUMNS]))
		{
			throw new Exception('The %tag tag is required', array('%tag' => 'T_COLUMNS'));
		}

		foreach ($tags as $tag => $value)
		{
			switch ($tag)
			{
				case self::T_COLUMNS:
				{
					foreach ($value as $identifier => &$column)
					{
						if (!$identifier)
						{
							continue;
						}

						$column += array
						(
							'label' => $identifier
						);
					}

					$this->columns = $value;
				}
				break;

				case self::T_KEY:
				{
					$this->idtag = $value;

					#
					# now that entries have a primary key, we can add the 'delete' job
					#

					$this->addJob(Module::OPERATION_DELETE, t('delete.operation.short_title'));
				}
				break;

				case self::T_JOBS:
				{
					foreach ($value as $operation => $label)
					{
						$this->addJob($operation, $label);
					}
				}
				break;
			}
		}

		$this->t = new Proxi(array('scope' => array($module->flat_id, 'manager')));
	}

	/**
	 * Renders the object into a HTML string.
	 */
	public function render()
	{
		global $core;

		static::handle_assets();

		$module_id = $this->module->id;
		$session = $core->session;

		$options = $this->retrieve_options($module_id);

		$modifiers = array_diff_assoc($_GET, $options);
		// FIXME: if modifiers ?

		$this->options = $this->update_options($options, $modifiers);

		$this->store_options($this->options, $module_id);

		#
		# load entries
		#

		list($conditions, $conditions_args) = $this->get_query_conditions($this->options);

		$query = $this->model->where(implode(' AND ', $conditions), $conditions_args);
		$query = $this->alter_query($query, $this->options['filters']);

		$this->count = $query->count;

		$query = $this->alter_range_query($query, $this->options);

		try
		{
			$records = $this->load_range($query);
		}
		catch (\Exception $e)
		{
			$options['order'] = array();
			$options['filters'] = array();

			$this->store_options($options, $module_id);

			return "There was an error in the SQL statement, orders and filters have been reseted,
			plase reload the page.<br /><br />" . $e->getMessage();
		}

		$this->entries = $this->alter_records($records);

		#
		# extend columns with additional information.
		#

		$this->columns = $this->extend_columns($this->columns);

		new Manager\AlterColumnsEvent($this, array('columns' => &$this->columns, 'records' => &$this->entries));

		$rc  = PHP_EOL;
		$rc .= '<form id="manager" method="get" action="">' . PHP_EOL;

		$rc .= new Element
		(
			'input', array
			(
				'name' => Operation::DESTINATION,
				'type' => 'hidden',
				'value' => (string) $this->module
			)
		);

		$rc .= new Element
		(
			'input', array
			(
				'name' => self::T_BLOCK,
				'type' => 'hidden',
				'value' => $this[self::T_BLOCK] ?: 'manage'
			)
		);

		if ($this->entries || $this->options['filters'])
		{
			if ($this->entries)
			{
				$body  = '<tbody>';
				$body .= $this->render_body();
				$body .= '</tbody>';
			}
			else
			{
				$body  = '<tbody class="empty"><tr><td colspan="' . count($this->columns) . '">' . $this->render_empty_body() . '</td></tr></tbody>';
			}

			$head = $this->render_head();
			$foot = $this->render_foot();

			$rc .= '<table class="group manage" cellpadding="4" cellspacing="0">';

			$rc .= $head . PHP_EOL . $foot . PHP_EOL . $body . PHP_EOL;

			$rc .= '</table>' . PHP_EOL;
		}
		else
		{
			$rc .= $this->render_empty_body();
		}

		$rc .= '</form>' . PHP_EOL;

		$search = $this->render_search();
		$browse = $this->browse;

		\ICanBoogie\Event\attach(function(ActionbarSearch\AlterInnerHTMLEvent $event, ActionbarSearch $sender) use($search, $browse) {

			$event->html .= $browse . $search;

		});

		return $rc;
	}

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('manager.js', -170);
		$document->css->add(\Icybee\ASSETS . 'css/manage.css', -170);
	}

	/**
	 * Retrieves previously used options.
	 *
	 * @param string $name Storage name for the options, usualy the module's id.
	 *
	 * @return array Previously used options, or brand new ones is none were defined.
	 */
	protected function retrieve_options($name)
	{
		global $core;

		$options = array
		(
			'start' => 1,
			'limit' => 10,
			'order' => array(),
			'search' => null,
			'filters' => array()
		);

		$session = $core->session;

		if (isset($session->manager[$name]))
		{
			$options = $session->manager[$name] + $options;
		}

		if (!$options['order'])
		{
			$order = $this[self::T_ORDER_BY];

			if ($order)
			{
				list($id, $direction) = ((array) $order) + array(1 => 'asc');

				if (is_string($direction))
				{
					$direction = $direction == 'desc' ? -1 : 1;
				}

				$options['order'] = array($id => $direction);
			}
			else
			{
				foreach ($this->columns as $id => $column)
				{
					if (empty($column['order']))
					{
						continue;
					}

					$options['order'] = array($id => isset($column['default_order_direction']) ? $column['default_order_direction'] : 1);

					break;
				}
			}
		}

		return $options;
	}

	/**
	 * Store options for later use.
	 *
	 * @param array $options The options to store.
	 * @param string $name Storage name for the options, usualy the module's id.
	 */
	protected function store_options(array $options, $name)
	{
		global $core;

		$core->session->manager[$name] = $options;
	}

	/**
	 * Updates options with the provided modifiers.
	 *
	 * The method updates the `order`, `start`, `limit`, `search` and `filters` options.
	 *
	 * The `start` options is reset to 1 when the `order`, `search` or `filters` options change.
	 *
	 * @param array $options Previous options.
	 * @param array $modifiers Options modifiers.
	 *
	 * @return array Updated options.
	 */
	protected function update_options(array $options, array $modifiers)
	{
		if (isset($modifiers['start']))
		{
			$options['start'] = max(filter_var($modifiers['start'], FILTER_VALIDATE_INT), 1);
		}

		if (isset($modifiers['limit']))
		{
			$options['limit'] = max(filter_var($modifiers['limit'], FILTER_VALIDATE_INT), 10);
		}

		if (isset($modifiers['search']))
		{
			$options['search'] = $modifiers['search'];
			$options['start'] = 1;
		}

		if (isset($modifiers['order']))
		{
			$order = $this->update_order($options['order'], $modifiers['order']);

			if ($order != $options['order'])
			{
				$options['start'] = 1;
			}

			$options['order'] = $order;
		}

		$filters = $this->update_filters($options['filters'], $modifiers);

		if ($filters != $options['filters'])
		{
			$options['filters'] = $filters;
			$options['start'] = 1;
		}

		return $options;
	}

	protected function update_order(array $order, $modifiers)
	{
		list($id, $direction) = explode(':', $modifiers) + array(1 => null);

		if (empty($this->columns[$id]))
		{
			return $order;
		}

		return array($id => $direction == 'desc' ? -1 : 1);
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
		static $as_strings = array('char', 'varchar', 'date', 'datetime', 'timestamp');

		$fields = $this->model->extended_schema['fields'];

		foreach ($modifiers as $identifier => $value)
		{
			if (empty($fields[$identifier]))
			{
				continue;
			}

			$type = $fields[$identifier]['type'];

			if ($type == 'boolean')
			{
				$value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			}
			else if ($type == 'integer')
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

		return $filters;
	}

	protected function parseColumns($columns)
	{
		foreach ($columns as $tag => &$column)
		{
			if (!is_array($column))
			{
				$column = array();
			}

			if (isset($column[self::COLUMN_HOOK]))
			{
				continue;
			}

			$callback = 'render_cell_' . $tag;

			if (method_exists($this, $callback))
			{
				$column[self::COLUMN_HOOK] = array($this, $callback);
			}
			else if (method_exists($this, 'get_cell_' . $tag))
			{
				$column[self::COLUMN_HOOK] = array($this, 'get_cell_' . $tag);
			}
			else
			{
				$column[self::COLUMN_HOOK] = array($this, 'render_raw_cell');
			}
		}

		#
		# key
		#

		if ($this->idtag)
		{
			$columns = array_merge
			(
				array
				(
					$this->idtag => array
					(
						'label' => null,
						'class' => 'key',
						self::COLUMN_HOOK => array($this, 'render_key_cell')
					)
				),

				$columns
			);
		}

		return $columns;
	}

	protected function get_query_conditions(array $options)
	{
		global $core;

		static $supported_types = array('varchar', 'text', 'timestamp', 'datetime', 'date');

		$where = array();
		$params = array();

		$display_search = $options['search'];

		$fields = $this->model->extended_schema['fields'];

		if ($display_search)
		{
			$words = explode(' ', $display_search);
			$words = array_map('trim', $words);

			$queries = array();

			foreach ($words as $word)
			{
				$concats = '';

				foreach ($fields as $identifier => $definition)
				{
					$type = $definition['type'];

					if (!in_array($type, $supported_types))
					{
						continue;
					}

					$concats .= ', `' . $identifier . '`';
				}

				if (!$concats)
				{
					continue;
				}

				$where[] = 'CONCAT_WS(" ", ' . substr($concats, 2) . ') LIKE ?';
				$params[] = '%' . $word . '%';
			}
		}

		foreach ($options['filters'] as $identifier => $value)
		{
			if (empty($fields[$identifier]))
			{
				continue;
			}

			$type = $fields[$identifier]['type'];

			if ($type == 'timestamp' || $type == 'date' || $type == 'datetime')
			{
				list($year, $month, $day) = explode('-', $value) + array(0, 0, 0);

				if ($year)
				{
					$where[] = "YEAR(`$identifier`) = ?";
					$params[] = (int) $year;
				}

				if ($month)
				{
					$where[] = "MONTH(`$identifier`) = ?";
					$params[] = (int) $month;
				}

				if ($day)
				{
					$where[] = "DAY(`$identifier`) = ?";
					$params[] = (int) $day;
				}
			}
			else
			{
				$where[] = "$identifier = ?";
				$params[] = $value;
			}
		}

		#
		# site
		#

		// TODO: move this to their respective manager

		if ($this->module instanceof \Icybee\Modules\Taxonomy\Vocabulary\Module)
		{
			$where['siteid'] = '(siteid = 0 OR siteid = ' . $core->site_id . ')';
		}

		return array($where, $params);
	}

	/**
	* Alters the initial query with the specified filters.
	*
	* @param Query $query
	* @param array $filters
	*
	* @return Query The altered query.
	*/
	protected function alter_query(Query $query, array $filters)
	{
		return $query;
	}

	protected function alter_range_query(Query $query, array $options)
	{
		$order = $options['order'];

		if ($order)
		{
			$o = '';

			foreach ($order as $id => $direction)
			{
				$o .= ', ' . $id . ' ' . ($direction < 0 ? 'DESC' : '');
			}

			$query->order(substr($o, 2));
		}

		return $query->limit($options['start'] - 1, $options['limit']);
	}

	protected function load_range(Query $query)
	{
		return $query->all;
	}

	protected function alter_records(array $records)
	{
		return $records;
	}


	protected function extend_columns(array $columns)
	{
		$fields = $this->model->extended_schema['fields'];

		foreach ($columns as $id => &$column)
		{
			$fallback = 'extend_column';
			$callback = $fallback . '_' . $id;

			if (!$this->has_method($callback))
			{
				$callback = $fallback;
			}

			$column = $this->$callback($column, $id, $fields);
		}

		return $columns;
	}

	/**
	 * Extends a column regarding filtering, ordering and more.
	 *
	 * @param array $options Initial options from columns definitions.
	 * @param string $id The identifier of the header cell.
	 * @param array $fields Fields of the extended schema of the model.
	 *
	 * @return array header cell options:
	 *
	 *    array|null filters The filter options available.
	 *    bool filtering true if the filter is currently used to filter the records, false otherwise.
	 *    string resets Query string to reset the filter.
	 *    mixed order null if the column is not used for ordering, -1 for descending ordering, 1
	 *    for ascending ordering.
	 *    bool sorted true if the column is sorted, false otherwise.
	 */
	protected function extend_column(array $column, $id, array $fields)
	{
		$options = $this->options;

		$orderable = !empty($column['label']);
		$order = isset($options['order'][$id]) ? $options['order'][$id] : null;
		$default_order = 1;
		$discreet = false;

		$field = isset($fields[$id]) ? $fields[$id] : null;

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

		return $column + array
		(
			'class' => null,

			'filters' => null,
			'filtering' => isset($options['filters'][$id]),
			'reset' => "?$id=",

			'orderable' => $orderable,
			'order' => $order,
			'default_order' => $default_order,

			'discreet' => $discreet
		);
	}

	public function addJob($job, $label)
	{
		$this->jobs[$job] = $label;
	}

	protected function getURL(array $modifier=array(), $fragment=null)
	{
		$url = '?' . http_build_query($modifier);

		if ($fragment)
		{
			$url .= '#' . $fragment;
		}

		$url = strtr($url, array('+' => '%20'));

		return \ICanBoogie\escape($url);
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
	 * @param array $cell
	 * @param string $id
	 *
	 * @return string The rendered THEAD cell.
	 */
	protected function render_column(array $column, $id)
	{
		$class = 'cell--' . \Brickrouge\normalize($id) . ' ' . $column['class'];

		if ($this->count > 1 || $this->options['filters'] || $this->options['search'])
		{
			$orderable = $column['orderable'];

			if ($orderable)
			{
				$class .= ' orderable';
			}

			$filtering = $column['filtering'];

			if ($filtering)
			{
				$class .= ' filtering';
			}

			$filters = $column['filters'];

			if ($filters)
			{
				$class .= ' filters';
			}
		}
		else
		{
			$orderable = false;
			$filtering = false;
			$filters = array();
		}

		$options = '';

		if ($filters)
		{
			$options = $this->render_column_options($filters, $id, $column);
			$class .= ' has-options';
		}

		$t = $this->t;

		$label = isset($column['label']) ? $column['label'] : null;

		if ($label)
		{
			$label = $t
			(
				$id, array(), array
				(
					'scope' => 'title',
					'default' => $t
					(
						$id, array(), array('scope' => '.label', 'default' => $label)
					)
				)
			);
		}

		if (!$label)
		{
			$class .= ' has-no-label';
		}

		$rc = '';

		if ($id == $this->idtag)
		{
			if ($this->checkboxes)
			{
				$rc .= new Element
				(
					'label', array
					(
						Element::CHILDREN => array
						(
							new Element
							(
								Element::TYPE_CHECKBOX
							)
						),

						'class' => 'checkbox-wrapper rectangle',
						'title' => t('Toggle selection for the entries ([alt] to toggle selection)')
					)
				);
			}
			else
			{
				$rc .= '&nbsp;';
			}
		}
		else
		{
			if ($filtering)
			{
				$rc .= '<a href="' . $column['reset'] . '" title="' . $t('View all') . '"><span class="title">' . ($label ? $label : '&nbsp;') . '</span></a>';
			}
			else if ($label && $orderable)
			{
				$order = $column['order'];
				$reverse = ($order === null) ? $column['default_order'] : -$order;

				$rc .= new Element
				(
					'a', array
					(
						Element::INNER_HTML => '<span class="title">' . $label . '</span>',

						'title' => $t('Sort by: :identifier', array(':identifier' => $label)),
						'href' => "?order=$id:" . ($reverse < 0 ? 'desc' : 'asc'),
						'class' => $order ? ($order < 0 ? 'desc' : 'asc') : null
					)
				);
			}
			else if ($label)
			{
				$rc .= $label;
			}
		}

		$class = trim($class);

		return <<<EOT
<th class="$class"><div>{$rc}{$options}</div></th>
EOT;
	}

	/**
	 * Renders a column filter.
	 *
	 * @param array|string $filter
	 * @param string $id
	 * @param array $header
	 */
	protected function render_column_options($filter, $id, $header)
	{
		$options = array();

		if ($header['filtering'])
		{
			$options[$header['reset']] = t('Display all');
			$options[] = false;
		}

		foreach ($filter['options'] as $qs => $label)
		{
			if ($qs[0] == '=')
			{
				$qs = $id . $qs;
			}

			$options['?' . $qs] = t($label);
		}

		$dropdown_menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $header['filtering']
			)
		);

		return <<<EOT
<div class="dropdown navbar"><a href="#" data-toggle="dropdown"><i class="icon-cog"></i></a>$dropdown_menu</div>
EOT;
	}

	protected function render_body()
	{
		global $core;

		$user = $core->user;
		$module = $this->module;
		$idtag = $this->idtag;

		$rc = '';

		foreach ($this->entries as $record)
		{
			$class = '';

			$ownership = $idtag ? $user->has_ownership($module, $record) : null;

			if ($ownership === false)
			{
				$class .= ' no-ownership';
			}

			$rc .= '<tr ' . ($class ? 'class="' . $class . '"' : '') . '>';

			foreach ($this->columns as $id => $column)
			{
				$rc .= $this->render_cell($record, $id, $column) . PHP_EOL;
			}

			$rc .= '</tr>';
		}

		return $rc;
	}

	protected function render_empty_body()
	{
		global $core;

		$search = $this->options['search'];
		$context = null;

		if ($search)
		{
			$message = t('Your search <q><strong>!search</strong></q> did not match any record.', array('!search' => $search));
		}
		else if ($this->options['filters'])
		{
			$filters = implode(', ', $this->options['filters']);
			$message = t('Your selection <q><strong>!selection</strong></q> dit not match any record.', array('!selection' => $filters));
		}
		else
		{
			$message = t('manager.create_first', array('!url' => $core->site->path . '/admin/' . $this->module . '/new'));
			$context = 'info';
		}

		return (string) new Alert($message, array(Alert::CONTEXT => $context));
	}

	protected $last_rendered_cell = array();

	protected function render_cell($record, $property, array $column)
	{
		try
		{
			$content = call_user_func($column[self::COLUMN_HOOK], $record, $property, $this);
		}
		catch (\Exception $e)
		{
			$content = '<span class="small error">' . $e->getMessage() . '</span>';
		}

		if ($column['discreet'])
		{
			if (isset($this->last_rendered_cell[$property]) && $this->last_rendered_cell[$property] === $content)
			{
				$content = self::REPEAT_PLACEHOLDER;
			}
			else
			{
				$this->last_rendered_cell[$property] = $content;
			}
		}

		$class = 'cell--' . \Brickrouge\normalize($property) . ' ' . $column['class'];

		return '<td class="' . trim($class) . '">' . $content . '</td>';
	}

	protected function render_key_cell(ActiveRecord $record, $property)
	{
		global $core;

		$disabled = true;

		if ($core->user->has_ownership($this->module, $record))
		{
			$disabled = false;

			$this->checkboxes++;
		}

		$value = $record->$property;

		return new Element
		(
			'label', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							'value' => $value,
							'checked' => $disabled
						)
					)
				),

				'title' => t('Toggle selection for record #:key', array('key' => $value)),
				'class' => 'checkbox-wrapper rectangle'
			)
		);
	}

	/**
	 * Renders a cell which content can be used to filter a column.
	 *
	 * @param ActiveRecord $record
	 * @param string $property
	 * @param null|string $label Defines the label for the filter link. If null the value of the
	 * property is used instead. If the value of the property is used it is escapted using the
	 * {@link \ICanBoogie\escape()} function, otherwise the label is use as is.
	 *
	 * @return string
	 */
	protected function render_filter_cell($record, $property, $label=null, $value=null)
	{
		if ($value === null)
		{
			$value = $record->$property;
		}

		if ($label === null)
		{
			$label = \ICanBoogie\escape($value);
		}

		if (isset($this->options['filters'][$property]))
		{
			return $label;
		}

		$title = \ICanBoogie\escape(t('Display only: :identifier', array(':identifier' => strip_tags($label))));
		$url = \ICanBoogie\escape($property . '=') . urlencode($value);

		return <<<EOT
<a class="filter" href="?$url" title="$title">$label</a>
EOT;
	}

	protected function render_edit_cell($record, $property, $label=null, $key=null)
	{
		global $core;

		$value = $record->$property;

		if ($label === null)
		{
			$label = \ICanBoogie\escape($value);
		}

		if ($key === null)
		{
			$key = $record->{$this->idtag};
		}

		$title = \ICanBoogie\escape(t('Display only: :identifier', array(':identifier' => strip_tags($label))));

		return <<<EOT
<a class="edit" href="{$core->site->path}/admin/{$this->module}/$key/edit" title="$title">$label</a>
EOT;
	}

	protected function render_raw_cell($record, $property)
	{
		return \ICanBoogie\escape($record->$property);
	}

	/**
	 * Renders cell value as time.
	 *
	 * @param ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_time($record, $property)
	{
		$value = $record->$property;

		if (preg_match('#(\d{2})\:(\d{2})\:(\d{2})#', $value, $time))
		{
			return $time[1] . ':' . $time[2];
		}
	}

	/**
	 * Renders cell value as date.
	 *
	 * @param ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_date($record, $property)
	{
		$tag = $property;
		$value = substr($record->$property, 0, 10);

		if (isset($this->last_rendered_cell[$property]) && $value == $this->last_rendered_cell[$property])
		{
			return self::REPEAT_PLACEHOLDER;
		}

		$this->last_rendered_cell[$property] = $value;

		if (!(int) $value || !preg_match('#(\d{4})-(\d{2})-(\d{2})#', $value, $date))
		{
			return;
		}

		list(, $year, $month, $day) = $date;


		$filtering = false;
		$filter = null;

		if (isset($this->options['filters'][$property]))
		{
			$filtering = true;
			$filter = $this->options['filters'][$property];
		}

		$parts = array
		(
			array($year, $year),
			array($month, "$year-$month"),
			array($day, "$year-$month-$day")
		);

		$today = date('Y-m-d');
		$today_year = substr($today, 0, 4);
		$today_month = substr($today, 5, 2);
		$today_day = substr($today, 8, 2);

		$select = $parts[2][1];
		$diff_days = $day - $today_day;

		if ($year == $today_year && $month == $today_month && $day <= $today_day && $day > $today_day - 6)
		{
			$label = \ICanBoogie\I18n\date_period($value);
			$label = ucfirst($label);

			if ($filtering && $filter == $today)
			{
				$rc = $label;
			}
			else
			{
				$ttl = t('Display only: :identifier', array(':identifier' => $label));

				$rc = <<<EOT
<a href="?$property=$select" title="$ttl" class="filter">$label</a>
EOT;
			}
		}
		else
		{
			$rc = '';

			foreach ($parts as $i => $part)
			{
				list($value, $select) = $part;

				if ($filtering && $filter == $select)
				{
					$rc .= $value;
				}
				else
				{
					$ttl = t('Display only: :identifier', array(':identifier' => $select));

					$rc .= <<<EOT
<a class="filter" href="?$property=$select" title="$ttl">$value</a>
EOT;
				}

				if ($i < 2)
				{
					$rc .= '–';
				}
			}
		}

		return $rc;
	}

	/**
	 * Renders the value as date and time.
	 *
	 * @param ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_datetime($record, $property)
	{
		$date = $this->render_cell_date($record, $property);
		$time = $this->render_cell_time($record, $property);

		return $date . ($time ? '&nbsp;<span class="small light">' . $time . '</span>' : '');
	}

	/**
	 * Renders cell value as size.
	 *
	 * @param ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_size($record, $property)
	{
		$label = \ICanBoogie\I18n\format_size($record->$property);

		if (isset($this->last_rendered_cell[$property]) && $this->last_rendered_cell[$property] === $label)
		{
			return self::REPEAT_PLACEHOLDER;
		}

		$this->last_rendered_cell[$property] = $label;

		return $label;
	}

	/**
	 * Renders the "search" element to be injected in the document.
	 *
	 * @return string The rendered "search" element.
	 */
	protected function render_search()
	{
		$search = $this->options['search'];

		$html = (string) new Form
		(
			array
			(
				Element::CHILDREN => array
				(
					'search' => new Text
					(
						array
						(
							'title' => t('Search in the records'),
							'value' => $search,
							'size' => '16',
							'class' => 'search' . ($search ? '' : ' empty'),
							'tabindex' => 0,

							'data-placeholder' => t('Search')
						)
					),

					new Button
					(
						'×', array
						(
							'type' => 'button',
							'class' => 'icon-remove'
						)
					)
				),

				'class' => 'navbar-search search' . ($search ? ' active' : ''),
				'method' => \ICanBoogie\HTTP\Request::METHOD_GET
			)
		);

		$this->rendered_search = $html;

		return $html;
	}

	protected function render_limiter()
	{
		$t = $this->t;

		$count = $this->count;
		$start = $this->options['start'];
		$limit = $this->options['limit'];

		$ranger = new Ranger
		(
			'span', array
			(
				Ranger::T_START => $start,
				Ranger::T_LIMIT => $limit,
				Ranger::T_COUNT => $count,
				Ranger::T_EDITABLE => true,
				Ranger::T_NO_ARROWS => true
			)
		);

		$page_limit_selector = null;

		if (($limit >= 20) || ($count >= $limit))
		{
			$page_limit_selector = new Element
			(
				'select', array
				(
					Element::OPTIONS => array(10 => 10, 20 => 20, 50 => 50, 100 => 100),

					'title' => $t('Number of item to display by page'),
					'name' => 'limit',
					'onchange' => 'this.form.submit()',
					'value' => $limit
				)
			);

			$page_limit_selector = ' &nbsp; ' . $t(':page_limit_selector by page', array(':page_limit_selector' => (string) $page_limit_selector));
		}

		$browse = null;

		if ($count > $limit)
		{
			$previous = ($start - $limit < 1 ? $count - $limit + 1 + ($count % $limit ? $limit - ($count % $limit) : 0) : $start - $limit);
			$next = ($start + $limit > $count ? 1 : $start + $limit);

			$browse = <<<EOT
<span class="browse">
	<a href="?start=$previous" class="browse previous">&lt;</a>
	<a href="?start=$next" class="browse next">&gt;</a>
</span>
EOT;
		}

		$this->browse = $browse;

		# the hidden select is a trick for vertical alignement with the operation select

		return <<<EOT
<div class="limiter">
	<select style="visibility: hidden;"><option>&nbsp;</option></select>
	{$ranger}{$page_limit_selector}{$browse}
</div>
EOT;
	}

	protected function getJobs()
	{
		if (!$this->jobs)
		{
			return;
		}

		$options = array(null => t('For the selection…', array(), array('scope' => 'manager')));

		foreach ($this->jobs as $operation => $label)
		{
			$options[$operation] = $label;
		}

		return new Element
		(
			'div', array
			(
				Element::CHILDREN => array
				(
					'jobs' => new Element
					(
						'select', array
						(
							Element::OPTIONS => $options
						)
					)
				),

				'class' => 'jobs'
			)
		);
	}

	protected function render_foot()
	{
		$rc  = '<tfoot>';
		$rc .= '<tr>';

		if ($this->idtag)
		{
			$rc .= '<td class="key">&nbsp;</td>';
		}

		$ncolumns = count($this->columns);

		#
		# operations
		#

		// +1 for the 'operation' column apparently

		$rc .= '<td colspan="' . $ncolumns . '">';

		$rc .= $this->entries ? $this->getJobs() : '';
		$rc .= $this->count ? $this->render_limiter() : '';

		$rc .= '</td>';

		$rc .= '</tr>';
		$rc .= '</tfoot>';
		$rc .= PHP_EOL;

		return $rc;
	}

	const MODIFY_MAX_LENGTH = 48;

	static public function modify_callback($entry, $tag, $resume)
	{
		global $core;

		$label = $entry->$tag;

		if (mb_strlen($label) > self::MODIFY_MAX_LENGTH)
		{
			$label = \ICanBoogie\escape(trim(mb_substr($label, 0, self::MODIFY_MAX_LENGTH))) . '…';
		}
		else
		{
			$label = \ICanBoogie\escape($entry->$tag);
		}

		$title = $core->user->has_ownership($resume->module, $entry) ? 'Edit this item' : 'View this item';
		$key = $resume->idtag;
		$path = $resume->module;

		return new Element
		(
			'a', array
			(
				Element::INNER_HTML => $label,

				'class' => 'edit',
				'title' => t($title),
				'href' => $core->site->path . '/admin/' . $path . '/' . $entry->$key . '/edit'
			)
		);
	}

	static public function modify_code($label, $key, $resume)
	{
		global $core;

		return new Element
		(
			'a', array
			(
				Element::INNER_HTML => $label,

				'class' => 'edit',
				'title' => t('Edit this item'),
				'href' => $core->site->path . '/admin/' . $resume->module . '/' . $key . '/edit'
			)
		);
	}

	protected function render_cell_boolean($record, $property)
	{
		return $this->render_filter_cell($record, $property, $record->$property ? $this->t->__invoke('Yes') : '');
	}

	protected function render_cell_email($record, $property)
	{
		$email = $record->$property;

		return '<a href="mailto:' . $email . '" title="' . t('Send an E-mail') . '">' . $email . '</a>';
	}
}

namespace Icybee\Manager;

/**
 * Event class for the `Icybee\Manager::alter_columns` event.
 */
class AlterColumnsEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the columns of the element.
	 *
	 * @var array[string]array
	 */
	public $columns;

	/**
	 * Reference to the records displayed by the element.
	 *
	 * @var array
	 */
	public $records;

	/**
	 * The event is constructed with the type `alter_columns`.
	 *
	 * @param \Icybee\Manager $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Manager $target, array $payload)
	{
		parent::__construct($target, 'alter_columns', $payload);
	}
}