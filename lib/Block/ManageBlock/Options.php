<?php

namespace Icybee\Block\ManageBlock;

use ICanBoogie\Accessor\AccessorTrait;

/**
 * Display options of the manager element.
 *
 * The metas of the current user are used to persist the options.
 *
 * @property-read int $site_id
 * @property-read \Icybee\Modules\Users\User $user
 */
class Options
{
	use AccessorTrait;

	public $start = 1;
	public $limit = 10;
	public $order_by = null;
	public $order_direction = null;
	public $search = null;
	public $filters = [];

	private $name;

	protected function get_site_id()
	{
		return \ICanBoogie\app()->site_id;
	}

	protected function get_user()
	{
		return \ICanBoogie\app()->user;
	}

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function __set($property, $value)
	{
		if ($property == 'order')
		{
			throw new \InvalidArgumentException("The <q>order</q> property is deprecated. Please use <q>order_by</q> and <q>order_direction</q>.");
		}

		$this->$property = $value;
	}

	/**
	 * Reset the options.
	 */
	public function reset()
	{
		$this->start = 1;
		$this->limit = 10;
		$this->order_by = null;
		$this->order_direction = null;
		$this->search = null;
		$this->filters = [];

		return $this;
	}

	/**
	 * Returns an array representation of the object.
	 *
	 * @return array
	 */
	public function to_array()
	{
		$array = get_object_vars($this);

		unset($array['name']);

		return $array;
	}

	/**
	 * Retrieves previously used options.
	 *
	 * @return array Previously used options, or brand new ones is none were defined.
	 */
	public function retrieve()
	{
		$this->reset();

		$serialized = $this->user->metas["block.manager.{$this->name}:{$this->site_id}"];

		if ($serialized)
		{
			$options = json_decode($serialized, true);

			foreach ($options as $option => $value)
			{
				$this->$option = $value;
			}
		}

		return $this;
	}

	/**
	 * Store options for later use.
	 */
	public function store()
	{
		$serialized = json_encode($this->to_array());

		$this->user->metas["block.manager.{$this->name}:{$this->site_id}"] = $serialized;

		return $this;
	}

	/**
	 * Update the options according to the specified modifiers.
	 *
	 * @param array $modifiers
	 *
	 * @return Options
	 */
	public function update(array $modifiers)
	{
		if (isset($modifiers['limit']))
		{
			$this->limit = max(filter_var($modifiers['limit'], FILTER_VALIDATE_INT), 10);
		}

		if (isset($modifiers['start']))
		{
			$start = $modifiers['start'];

			if ($start === 'next')
			{
				$this->start += $this->limit;
			}
			else if ($start === 'previous')
			{
				$this->start -= $this->limit;
			}
			else
			{
				$this->start = max(filter_var($start, FILTER_VALIDATE_INT), 1);
			}
		}

		if (isset($modifiers['q']))
		{
			$this->search = $modifiers['q'];
			$this->start = 1;
		}

		if (isset($modifiers['order']))
		{
			list($order_by, $order_direction) = explode(':', $modifiers['order']) + [ 1 => null ];

			$order_direction = (strtolower($order_direction) == 'desc' ? -1 : 1);

			if ($order_by != $this->order_by  || $order_direction != $this->order_direction)
			{
				$this->start = 1;
			}

			$this->order_by = $order_by;
			$this->order_direction = $order_direction;
		}

		if (isset($modifiers['filters']))
		{
			$filters = $modifiers['filters'];

			if ($this->filters != $filters)
			{
				$this->filters = $filters;
				$this->start = 1;
			}
		}

		return $this;
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
		if ($column_id === null)
		{
			return !empty($this->filters);
		}

		return isset($this->filters[$column_id]);
	}
}
