<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Journal;

use ICanBoogie\ActiveRecord\Query;

class ManageBlock extends \Icybee\ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_ORDER_BY => array('timestamp', 'desc')
			)
		);
	}

	protected function columns()
	{
		return array
		(
			'message' => array
			(
				'label' => 'Message',
				'discreet' => true
			),

			'severity' => array
			(
				'label' => 'Severity',
				'discreet' => true
			),

			'type' => array
			(
				'label' => 'Type',
				'discreet' => true
			),

			'class' => array
			(
				'label' => 'Class',
				'discreet' => true
			),

			'uid' => array
			(
				'sortable' => true
			),

			'timestamp' => array
			(
				'label' => 'Date',
				'class' => 'date'
			)
		);
	}

	protected function get_query_conditions(array $options)
	{
		unset($options['filters']['class']);

		return  parent::get_query_conditions($options);
	}

	protected function alter_query(Query $query, array $filters)
	{
		$class = null;

		if (!empty($filters['class']))
		{
			$class = $filters['class'];

			unset($filters['class']);
		}

		$query = parent::alter_query($query, $filters);

		if ($class)
		{
			list($type, $name) = explode(':', $class);

			if ($type == 'operation')
			{
				$query->where('class LIKE ?', '%\\' . $name . 'Operation');
			}
		}

		return $query;
	}

	protected function render_cell_message($record, $property)
	{
		return $record->$property;
	}

	protected function render_cell_severity($record, $property)
	{
		static $labels = array
		(
			Entry::SEVERITY_DEBUG => '<span class="label label-debug">debug</span>',
			Entry::SEVERITY_INFO => '<span class="label label-info">info</span>',
			Entry::SEVERITY_WARNING => '<span class="label label-warning">warning</span>',
			Entry::SEVERITY_DANGER => '<span class="label label-danger">danger</span>'
		);

		$value = $record->$property;
		$label = $labels[$value];

		return $this->render_filter_cell($record, $property, $label);
	}

	protected function render_cell_type($record, $property)
	{
		return $this->render_filter_cell($record, $property, $this->t($record->$property, array(), array('scope' => 'type')));
	}

	/**
	 * Extends the "uid" column by providing users filters.
	 *
	 * @param array $column
	 * @param string $id
	 */
	protected function extend_column_uid(array $column, $id, array $fields)
	{
		global $core;

		$users_keys = $this->module->model->select('DISTINCT uid')->where('siteid = ?', $core->site_id)->all(\PDO::FETCH_COLUMN);

		if (!$users_keys || count($users_keys) == 1)
		{
			return array
			(
				'sortable' => false
			)

			+ parent::extend_column($column, $id, $fields);
		}

		$users = $core->models['users']->select('CONCAT("=", uid), IF((firstname != "" AND lastname != ""), CONCAT_WS(" ", firstname, lastname), username) name')->where(array('uid' => $users_keys))->order('name')->pairs;

		return array
		(
			'filters' => array
			(
				'options' => $users
			)
		)

		+ parent::extend_column($column, $id, $fields);
	}

	private $last_rendered_uid;

	protected function render_cell_uid($record, $property)
	{
		$uid = $record->uid;

		if ($this->last_rendered_uid === $uid)
		{
			return self::REPEAT_PLACEHOLDER;
		}

		$this->last_rendered_uid = $uid;

		if ($uid)
		{
			$label = $this->render_cell_user($record, $property);
		}
		else
		{
			$label = $this->t('Guest');
		}

		return parent::render_filter_cell($record, $property, $label);
	}

	protected function render_cell_timestamp($record, $property)
	{
		return $this->render_cell_datetime($record, $property);
	}

	protected function render_cell_class($record, $property)
	{
		$class_name = $record->$property;

		if (is_subclass_of($class_name, 'ICanBoogie\Operation'))
		{
			$path = strtr($class_name, '\\', '/');
			$basename = basename($path, 'Operation');

			return $this->render_filter_cell($record, $property, $basename, 'operation:' . $basename);
		}
	}
}