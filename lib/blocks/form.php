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

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

/**
 * Base class for form type blocks.
 */
abstract class FormBlock extends Form
{
	/**
	 * Module requesting the block.
	 *
	 * @var Module
	 */
	protected $module;

	/**
	 * Constructor.
	 *
	 * @param Module $module
	 * @param array $attributes
	 */
	public function __construct(Module $module, array $attributes=array())
	{
		$this->module = $module;

		$this->access_control();

		$values = isset($attributes[self::VALUES]) ? $attributes[self::VALUES] : array();
		$children = $this->children;
		$actions = array
		(
			new Button
			(
				'Send', array
				(
					'class' => 'btn-primary',
					'type' => 'submit'
				)
			)
		);

		$attributes += array
		(
			self::ACTIONS => &$actions,

			self::RENDERER => new \Brickrouge\Renderer\Simple
			(
				array
				(
					\Brickrouge\Renderer\Simple::GROUP_CLASS => 'Icybee\Element\Group'
				)
			),

			self::VALUES => &$values,
			self::CHILDREN => &$children,

			'id' => 'editor',
			'action' => '',
			'class' => 'form-primary edit',
			'name' => (string) $module
		);

		$alter_params = array
		(
			'module' => $module,
			'attributes' => &$attributes
		);

		$this->fire_before_alter_attributes($alter_params);
		$attributes = $this->alter_attributes($attributes);
		$this->fire_alter_attributes($alter_params);

		$alter_params['values'] = &$values;

		$this->fire_before_alter_values($alter_params);
		$values = $alter_params['values'] = $this->alter_values($values, $attributes);
		$this->fire_alter_values($alter_params);

		$alter_params['children'] = &$children;

		$this->fire_before_alter_children($alter_params);
		$children = $alter_params['children'] = $this->alter_children($children, $values, $attributes);
		$this->fire_alter_children($alter_params);

		$alter_params['actions'] = &$actions;

		$this->fire_before_alter_actions($alter_params);
		$actions = $alter_params['actions'] = $this->alter_actions($actions);
		$this->fire_alter_actions($alter_params);

		if (!$this->permission)
		{
			$attributes[self::ACTIONS] = null;
			$attributes[self::DISABLED] = true;
		}

		parent::__construct($attributes);
	}

	abstract protected function __get_permission();
	abstract protected function access_control();

	/*
	 * ATTRIBUTES
	 */

	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			array
			(
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $this->module->id
				),

				Element::GROUPS => array
				(
					'primary' => array
					(

					)
				)
			)

			+ $attributes
		);
	}

	protected function fire_before_alter_attributes(array $params)
	{
		Event::fire('alter_attributes:before', $params, $this);
	}

	protected function fire_alter_attributes(array $params)
	{
		Event::fire('alter_attributes', $params, $this);
	}

	/*
	 * VALUES
	 */

	/**
	 * Alerts the editable properties of the record.
	 *
	 * @param array $properties
	 */
	protected function alter_values(array $properties, array &$attributes)
	{
		return $properties;
	}

	protected function fire_before_alter_values(array $params)
	{
		Event::fire('alter_values:before', $params, $this);
	}

	protected function fire_alter_values(array $params)
	{
		Event::fire('alter_values', $params, $this);
	}

	/*
	 * CHILDREN
	 */

	/**
	 * Alerts the children of the block.
	 *
	 * @param array $children
	 */
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return $children;
	}

	protected function fire_before_alter_children(array $params)
	{
		Event::fire('alter_children:before', $params, $this);
	}

	protected function fire_alter_children(array $params)
	{
		Event::fire('alter_children', $params, $this);
	}

	/*
	 * ACTIONS
	 */

	/**
	 * Alerts form actions
	 */
	protected function alter_actions(array $actions)
	{
		return $actions;
	}

	protected function fire_before_alter_actions(array $params)
	{
		Event::fire('alter_actions:before', $params, $this);
	}

	protected function fire_alter_actions(array $params)
	{
		Event::fire('alter_actions', $params, $this);
	}
}