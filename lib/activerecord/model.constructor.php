<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\ActiveRecord\Model;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\RecordNotFound;

/**
 * This is the super class for all models using constructors (currently "nodes" and "users").
 * It provides support for the `constructor` property whethers it is for saving records or
 * filtering them throught the `own` scope.
 */
class Constructor extends \ICanBoogie\ActiveRecord\Model
{
	const T_CONSTRUCTOR = 'constructor';

	protected $constructor;

	public function __construct(array $attributes)
	{
		if (empty($attributes[self::T_CONSTRUCTOR]))
		{
			throw new \Exception('The T_CONSTRUCTOR tag is required');
		}

		$this->constructor = $attributes[self::T_CONSTRUCTOR];

		parent::__construct($attributes);
	}

	/**
	 * Overwrites the `constructor` property of new records.
	 */
	public function save(array $properties, $key=null, array $options=array())
	{
		if (!$key && empty($properties[self::T_CONSTRUCTOR]))
		{
			$properties[self::T_CONSTRUCTOR] = $this->constructor;
		}

		return parent::save($properties, $key, $options);
	}

	/**
	 * Makes sure that records are found using their true constructor.
	 */
	public function find($key)
	{
		$args = func_get_args();
		$record = call_user_func_array('parent::' . __FUNCTION__, $args);

		if ($record instanceof \ICanBoogie\ActiveRecord)
		{
			$entry_model = \ICanBoogie\ActiveRecord\get_model($record->constructor);

			if ($this !== $entry_model)
			{
				$record = $entry_model[$key];
			}
		}

		return $record;
	}

	/**
	 * Finds records using their constructor.
	 *
	 * Unlike {@link find()} this method is designed to find records that where created by
	 * different constructors. The result is the same, bu where {@link find()} uses a new request
	 * for each record that is not created by the current model, this method only needs one query
	 * by constructor plus one extra query.
	 *
	 * @param array $keys
	 *
	 * @throws RecordNotFound If a record was not found.
	 *
	 * @return array
	 */
	public function find_using_constructor(array $keys)
	{
		if (!$keys)
		{
			return array();
		}

		$records = array_combine($keys, array_fill(0, count($keys), null));
		$missing = $records;

		$constructors = $this
		->select('constructor, {primary}')
		->where(array('{primary}' => $keys))
		->all(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP);

		foreach ($constructors as $constructor => $constructor_keys)
		{
			try
			{
				$constructor_records = \ICanBoogie\ActiveRecord\get_model($constructor)->find($constructor_keys);

				foreach ($constructor_records as $key => $record)
				{
					$records[$key] = $record;
					unset($missing[$key]);
				}
			}
			catch (RecordNotFound $e)
			{
				foreach ($e->records as $key => $record)
				{
					if ($record === null)
					{
						continue;
					}

					$records[$key] = $record;
					unset($missing[$key]);
				}
			}
		}

		if ($missing)
		{
			if (count($missing) > 1)
			{
				throw new RecordNotFound
				(
					"Records " . implode(', ', array_keys($missing)) . " do not exists.", $records
				);
			}
			else
			{
				$key = array_keys($missing);
				$key = array_shift($key);

				throw new RecordNotFound
				(
					"Record <q>{$key}</q> does not exists.", $records
				);
			}
		}

		return $records;
	}

	/**
	 * Adds the "constructor = <constructor>" condition to the query.
	 *
	 * @param Query $query The query to alter.
	 *
	 * @return Query
	 */
	protected function scope_own(Query $query)
	{
		return $query->filter_by_constructor($this->constructor);
	}
}