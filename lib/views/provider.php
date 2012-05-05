<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Views;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use ICanBoogie\Module;

abstract class Provider
{
	const RETURNS_ONE = 1;
	const RETURNS_MANY = 2;
	const RETURNS_OTHER = 3;

	protected $view;
	protected $context;
	protected $module;
	protected $conditions;
	protected $returns;

	public function __construct(View $view, \BlueTihi\Context $context, Module $module, array $conditions, $returns)
	{
		$this->view = $view;
		$this->context = $context;
		$this->module = $module;
		$this->conditions = $conditions;
		$this->returns = $returns;
	}

	abstract public function __invoke();

	/**
	 * Alters the conditions.
	 *
	 * @param array $conditions
	 */
	abstract protected function alter_conditions(array $conditions);

	/**
	 * Alters rendering context.
	 *
	 * @param array $context
	 */
	abstract protected function alter_context(\BlueTihi\Context $context, Query $query, array $conditions);
}

namespace Icybee\Views\ActiveRecord;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;

abstract class Provider extends \Icybee\Views\Provider
{
	/**
	 * @return array[ActiveRecord]|ActiveRecord|null
	 *
	 * @see BriskView.Provider::__invoke()
	 */
	public function __invoke()
	{
		$conditions = &$this->conditions;

		$this->fire_alter_conditions
		(
			array
			(
				'conditions' => &$conditions,
				'view' => $this->view,
				'module' => $this->module
			)
		);

		$query = $this->create_query();

		$this->fire_alter_query
		(
			array
			(
				'query' => &$query,
				'conditions' => &$conditions,
				'view' => $this->view,
				'module' => $this->module
			)
		);

		$this->fire_alter_context
		(
			array
			(
				'context' => &$this->context,
				'query' => &$query,
				'conditions' => &$conditions,
				'view' => $this->view,
				'module' => $this->module
			)
		);

		$rc = $this->extract_result($query);

		/*
		$this->fire_alter_result
		(
			array
			(
				'result' => &$rc,
				'context' => &$this->context,
				'query' => &$query,
				'conditions' => &$conditions,
				'view' => $this->view,
				'module' => $this->module
			)
		);
		*/

		new Provider\AlterResultEvent
		(
			$this, array
			(
				'result' => &$rc,
				'context' => &$this->context,
				'query' => &$query,
				'conditions' => &$conditions,
				'view' => $this->view,
				'module' => $this->module
			)
		);

		return $rc;
	}

	/**
	 * Wraps a call to the #{@link alter_conditions()} method with the
	 * `alter_conditions:before` and `alter_conditions` events.
	 *
	 * The following properties are used for the event, subclasses might add others:
	 *
	 * - &conditions (array): The conditions to alter.
	 * - view (View): The view calling the provider.
	 * - module (Module): The module associated with the view.
	 *
	 * @param array $params
	 */
	protected function fire_alter_conditions(array $properties)
	{
		new Provider\BeforeAlterConditionsEvent($this, $properties);
		$properties['conditions'] = $this->alter_conditions($properties['conditions']);
		new Provider\AlterConditionsEvent($this, $properties);

		return $properties['conditions'];
	}

	/**
	 * Alters the activerecord query using the provided conditions.
	 *
	 * @param Query $query
	 * @param array $conditions
	 *
	 * @return Query
	 */
	protected function alter_query(Query $query, array $conditions)
	{
		return $query;
	}

	/**
	 * Wraps a call to the #{@link alter_query()} method with the
	 * `alter_query:before` and `alter_query` events.
	 *
	 * The following parameters are used for the event, subclasses might add others:
	 *
	 * - &query (Query): The query to alter.
	 * - view (View): The view calling the provider.
	 * - module (Module): The module associated with the view.
	 *
	 * @param array $params
	 */
	protected function fire_alter_query(array $params)
	{
		Event::fire('alter_query:before', $params, $this);
		$params['query'] = $this->alter_query($params['query'], $params['conditions']);
		Event::fire('alter_query', $params, $this);

		return $params['query'];
	}

	/**
	 * Wraps a call to the #{@link alter_context()} method with the
	 * `alter_context:before` and `alter_context` events.
	 *
	 * The following parameters are used for the event, subclasses might add others:
	 *
	 * - &context (array): The context.
	 * - &query (Query): The query to alter.
	 * - view (View): The view calling the provider.
	 * - module (Module): The module associated with the view.
	 *
	 * @param array $params
	 */
	protected function fire_alter_context(array $params)
	{
		Event::fire('alter_context:before', $params, $this);
		$params['context'] = $this->alter_context($params['context'], $params['query'], $params['conditions']);
		Event::fire('alter_context', $params, $this);

		return $params['context'];
	}

	/**
	 * Create the activerecord query object.
	 *
	 * @return Query
	 */
	protected function create_query()
	{
		return new Query($this->module->model);
	}

	protected function count_result(Query $query)
	{
		$range = $this->view->range;

		$page = $range['page'];
		$limit = $range['limit'];

		$count = $query->count;

		return $count;
	}

	protected function limit_result(Query $query, $limit, $page)
	{
		return $query->limit($page * $limit, $limit);
	}

	protected function extract_result(Query $query)
	{
		if ($this->returns == self::RETURNS_MANY)
		{
			$range = &$this->view->range;
			$range['count'] = $this->count_result($query);

			$query = $this->limit_result($query, $range['limit'], $range['page']);
		}

		return $query->all;
	}
}

namespace Icybee\Views\ActiveRecord\Provider;

/**
 * The event class for the `Icybee\Views\ActiveRecord\Provider::alter_conditions:before` event.
 */
class BeforeAlterConditionsEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the conditions to alter.
	 *
	 * @var array
	 */
	public $conditions;

	/**
	 * The view invoked the provider.
	 *
	 * @var \Icybee\Views\View
	 */
	public $view;

	/**
	 * The module of the view.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * The event is constructed with the type `alter_conditions:before`.
	 *
	 * @param Icybee\Views\ActiveRecord\Provider $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Views\ActiveRecord\Provider $target, array $properties)
	{
		parent::__construct($target, 'alter_conditions:before', $properties);
	}
}

/**
 * The event class for the `Icybee\Views\ActiveRecord\Provider::alter_conditions` event.
 */
class AlterConditionsEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the conditions to alter.
	 *
	 * @var array
	 */
	public $conditions;

	/**
	 * The view invoked the provider.
	 *
	 * @var \Icybee\Views\View
	 */
	public $view;

	/**
	 * The module of the view.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * The event is constructed with the type `alter_conditions`.
	 *
	 * @param Icybee\Views\ActiveRecord\Provider $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Views\ActiveRecord\Provider $target, array $properties)
	{
		parent::__construct($target, 'alter_conditions', $properties);
	}
}

/**
 * The event class for the `Icybee\Views\ActiveRecord\Provider::alter_result` event.
 */
class AlterResultEvent extends \ICanBoogie\Event
{
	/**
	 * The view invoked the provider.
	 *
	 * @var \Icybee\Views\View
	 */
	public $view;

	/**
	 * The module of the view.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Reference to the conditions.
	 *
	 * @var array
	 */
	public $conditions;

	/**
	 * ActiveRecord query.
	 *
	 * @var \ICanBoogie\ActiveRecord\Query
	 */
	public $query;

	/**
	 * Rendering engine context.
	 *
	 * @var mixed
	 */
	public $context;

	/**
	 * The query result to alter.
	 *
	 * @var mixed
	 */
	public $result;

	/**
	 * The event is constructed with the type `alter_result`.
	 *
	 * @param \Icybee\Views\ActiveRecord\Provider $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Views\ActiveRecord\Provider $target, array $properties)
	{
		parent::__construct($target, 'alter_result', $properties);
	}
}