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

class Provider
{
	const RETURN_ONE = 1;
	const RETURN_MANY = 2;

	protected $view;
	protected $context;
	protected $module;
	protected $conditions;

	public function __construct(View $view, array &$context, Module $module, array $conditions=array())
	{
		$this->view = $view;
		$this->context = &$context;
		$this->module = $module;
		$this->conditions = $conditions;
	}

	public function __invoke()
	{

	}

	protected function alter_conditions(array $conditions)
	{
		return $conditions;
	}

	protected function alter_context(array $context)
	{
		return $context;
	}

	/**
	 * Returns wheter the provider return a value (RETURN_ONE) or an array of values (RETURN_MANY).
	 *
	 * @return int
	 */
	protected function get_return_type()
	{
		return self::RETURN_MANY;
	}
}

namespace Icybee\Views\ActiveRecord;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;

class Provider extends \Icybee\Views\Provider
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

		return $rc;
	}

	/**
	 * Wraps a call to the #{@link alter_conditions()} method with the
	 * `alter_conditions:before` and `alter_conditions` events.
	 *
	 * The following parameters are used for the event, subclasses might add others:
	 *
	 * - &conditions (array): The conditions to alter.
	 * - view (View): The view calling the provider.
	 * - module (Module): The module associated with the view.
	 *
	 * @param array $params
	 */
	protected function fire_alter_conditions(array $params)
	{
		Event::fire('alter_conditions:before', $params, $this);
		$params['conditions'] = $this->alter_conditions($params['conditions']);
		Event::fire('alter_conditions', $params, $this);

		return $params['conditions'];
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
	 * Returns RETURN_ONE if the view is of type "view".
	 *
	 * @see BriskView.Provider::get_return_type()
	 */
	protected function get_return_type()
	{
		if ($this->view->type == "view")
		{
			return self::RETURN_ONE;
		}

		return parent::get_return_type();
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
		if ($this->get_return_type() == self::RETURN_MANY)
		{
			$range = &$this->view->range;
			$range['count'] = $this->count_result($query);

			$query = $this->limit_result($query, $range['limit'], $range['page']);
		}

		return $query->all;
	}
}