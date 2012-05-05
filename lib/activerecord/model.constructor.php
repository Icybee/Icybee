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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Exception;

/**
 * This is the super class for all models using constructors (currently "nodes" and "users").
 * It provides support for the `constructor` property whethers it is for saving records or
 * filtering them throught the `own` scope.
 */
class Constructor extends Model
{
	const T_CONSTRUCTOR = 'constructor';

	protected $constructor;

	public function __construct($tags)
	{
		if (empty($tags[self::T_CONSTRUCTOR]))
		{
			throw new Exception('The T_CONSTRUCTOR tag is required');
		}

		$this->constructor = $tags[self::T_CONSTRUCTOR];

		parent::__construct($tags);
	}

	/**
	 * Overwrites the `constructor` property of new records.
	 *
	 * @see ICanBoogie\ActiveRecord.Model::save()
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
	 * We override the load() method to make sure that records are loaded using their true
	 * constructor.
	 *
	 * @see ICanBoogie\ActiveRecord.Model::load()
	 */
	public function find($key)
	{
		global $core;

		$args = func_get_args();
		$record = call_user_func_array('parent::' . __FUNCTION__, $args);

		if ($record instanceof ActiveRecord)
		{
			$entry_model = $core->models[$record->constructor];

			if ($this !== $entry_model)
			{
				$record = $entry_model[$key];
			}
		}

		return $record;
	}

	/**
	 * Adds the "constructor = <constructor>" condition to the query.
	 *
	 * @return ICanBoogie\ActiveRecord\Query
	 */
	protected function scope_own(Query $query)
	{
		return $query->where('constructor = ?', $this->constructor);
	}
}