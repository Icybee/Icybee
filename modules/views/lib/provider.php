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

use ICanBoogie\PropertyNotReadable;

/**
 * Provides data for a view.
 */
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

	public function __construct(View $view, \BlueTihi\Context $context, \ICanBoogie\Module $module, array $conditions, $returns)
	{
		$this->view = $view;
		$this->context = $context;
		$this->module = $module;
		$this->conditions = $conditions;
		$this->returns = $returns;
	}

	public function __get($property)
	{
		static $readers = array('returns');

		if (in_array($property, $readers))
		{
			return $this->$property;
		}

		throw new PropertyNotReadable($property);
	}

	abstract public function __invoke();

	/**
	 * Alters the conditions.
	 *
	 * @param array $conditions
	 */
	protected function alter_conditions(array $conditions)
	{
		return $conditions;
	}

	/**
	 * Alters rendering context.
	 *
	 * @param array $context
	 */
	protected function alter_context(\BlueTihi\Context $context, \ICanBoogie\ActiveRecord\Query $query, array $conditions)
	{
		return $context;
	}
}