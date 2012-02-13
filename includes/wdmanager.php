<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord\Query;

class WdManager extends \Icybee\Manager
{
	public function __construct($module, array $tags=array())
	{
		global $core;

		$model_id = 'primary';

		if (is_string($module))
		{
			list($module_id, $model_id) = explode('/', $module) + array(1 => $model_id);

			$module = $core->modules[$module_id];
		}

		$model = $module->model($model_id);

		#
		# Set the properties here so that they are available to the columns() method, and others.
		#

		$this->module = $module;
		$this->model = $model;

		#
		# columns
		#

		$columns = $this->columns();

		if (isset($tags[self::T_COLUMNS_ORDER]))
		{
			$columns = wd_array_sort_and_filter($tags[self::T_COLUMNS_ORDER], $columns);
		}

		/* TODO-20101019: move parse columns else where */

		if (isset($tags[self::T_KEY]))
		{
			$this->idtag = $tags[self::T_KEY];
		}

		$columns = $this->parseColumns($columns);

		parent::__construct
		(
			$module, $model, $tags + array
			(
				self::T_BLOCK => 'manage',
				self::T_COLUMNS => $columns
			)
		);

		#
		# TODO: move this to Icybee\Manager somewhere
		#

		$jobs = $this->jobs();

		foreach ($jobs as $operation => $label)
		{
			$this->addJob($operation, $label);
		}
	}

	protected function columns()
	{
		return array();
	}

	protected function jobs()
	{
		return array();
	}

	protected $user_cache = array();

	/**
	 * Alters records.
	 *
	 * If the 'uid' column exists a cache is prepared for the {@link render_cell_user()} method
	 * with the users objects associated with the displayed records.
	 *
	 * @see Icybee\Manager::alter_records()
	 */
	protected function alter_records(array $records)
	{
		global $core;

		if (isset($this->columns['uid']))
		{
			$keys = array();

			foreach ($records as $record)
			{
				if (!$record->uid)
				{
					continue;
				}

				$keys[$record->uid] = true;
			}

			if ($keys)
			{
				$this->user_cache = $core->models['users']->find(array_keys($keys));
			}
		}

		return $records;
	}

	protected function render_cell_user($record, $property)
	{
		$uid = $record->$property;

		if (empty($this->user_cache[$uid]))
		{
			return '<em class="error">' . t('Unknown user: %uid', array('%uid' => $uid)) . '</em>';
		}

		$user = $this->user_cache[$uid];

		return ($user->firstname && $user->lastname) ? $user->firstname . ' ' . $user->lastname : $user->username;
	}
}