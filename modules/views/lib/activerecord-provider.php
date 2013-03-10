<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;

abstract class ActiveRecordProvider extends Provider
{
	/**
	 * @return array[ActiveRecord]|ActiveRecord|null
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

		new ActiveRecordProvider\AlterResultEvent
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
	 * Wraps a call to the {@link alter_conditions()} method with the
	 * {@link ActiveRecordProvider\BeforeAlterConditionsEvent} and
	 * {@link ActiveRecordProvider\AlterConditionsEvent} events.
	 *
	 * @param array $payload Event payload.
	 *
	 * @return array The altered conditions.
	 */
	protected function fire_alter_conditions(array $payload)
	{
		new ActiveRecordProvider\BeforeAlterConditionsEvent($this, $payload);
		$payload['conditions'] = $this->alter_conditions($payload['conditions']);
		new ActiveRecordProvider\AlterConditionsEvent($this, $payload);

		return $payload['conditions'];
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
	 * Wraps a call to the {@link alter_query()} method with the
	 * {@link ActiveRecordProvider\BeforeAlterQueryEvent} and
	 * {@link ActiveRecordProvider\AlterQueryEvent} events.
	 *
	 * @param array $payload Event payload.
	 *
	 * @return array The altered query.
	 */
	protected function fire_alter_query(array $payload)
	{
		new ActiveRecordProvider\BeforeAlterQueryEvent($this, $payload);
		$payload['query'] = $this->alter_query($payload['query'], $payload['conditions']);
		new ActiveRecordProvider\AlterQueryEvent($this, $payload);

		return $payload['query'];
	}

	/**
	 * Wraps a call to the {@link alter_context()} method with the
	 * {@link ActiveRecordProvider\BeforeAlterContextEvent} and
	 * {@link ActiveRecordProvider\AlterContextEvent} events.
	 *
	 * @param array $payload Event payload.
	 *
	 * @return array The altered context.
	 */
	protected function fire_alter_context(array $payload)
	{
		new ActiveRecordProvider\BeforeAlterContextEvent($this, $payload);
		$payload['context'] = $this->alter_context($payload['context'], $payload['query'], $payload['conditions']);
		new ActiveRecordProvider\AlterContextEvent($this, $payload);

		return $payload['context'];
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

	/**
	 * Extracts a result from the query.
	 *
	 * The returned result depends on the return type:
	 *
	 * - If the return type is {@link RETURNS_ONE} the first record is returned.
	 *
	 * - If the return type is {@link RETURNS_MANY} a number of records is returned according
	 * to the range limit and range page, the `count` value of the range is updated with the
	 * number of records matching the query. The {@link count_result()} method is used for this.
	 *
	 * - Otherwise, all the records matching the query are returned.
	 *
	 * @return ActiveRecord|array[ActiveRecord]|null If the provider must return one record,
	 * the method returns an ActiveRecord, or null if no record matching the conditions could be
	 * found, otherwise the method returns an array of ActiveRecord.
	 */
	protected function extract_result(Query $query)
	{
		if ($this->returns == self::RETURNS_ONE)
		{
			return $query->one;
		}
		else if ($this->returns == self::RETURNS_MANY)
		{
			$range = &$this->view->range;
			$range['count'] = $this->count_result($query);

			$query = $this->limit_result($query, $range['limit'], $range['page']);
		}

		return $query->all;
	}
}

namespace Icybee\Modules\Views\ActiveRecordProvider;

abstract class AlterEvent extends \ICanBoogie\Event
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
	 * @var \Icybee\Modules\Views\View
	 */
	public $view;

	/**
	 * The module of the view.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Reference to the ActiveRecord query.
	 *
	 * @var \ICanBoogie\ActiveRecord\Query
	 */
	public $query;

	/**
	 * Reference to the rendering context.
	 *
	 * @var \BlueTihi\Context
	 */
	public $context;

	/**
	 * Reference to the result of the provider.
	 *
	 * @var mixed
	 */
	public $result;
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_conditions:before` event.
 */
class BeforeAlterConditionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_conditions:before`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_conditions:before', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_conditions` event.
 */
class AlterConditionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_conditions`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_conditions', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_query:before` event.
 */
class BeforeAlterQueryEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_query:before`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_query:before', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_query` event.
 */
class AlterQueryEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_query`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_query', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_context:before` event.
 */
class BeforeAlterContextEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_context:before`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_context:before', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_context` event.
 */
class AlterContextEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_context`.
	 *
	 * @param Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_context', $payload);
	}
}

/**
 * The event class for the `Icybee\Modules\Views\ActiveRecordProvider::alter_result` event.
 */
class AlterResultEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_result`.
	 *
	 * @param \Icybee\Modules\Views\ActiveRecordProvider $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Views\ActiveRecordProvider $target, array $payload)
	{
		parent::__construct($target, 'alter_result', $payload);
	}
}