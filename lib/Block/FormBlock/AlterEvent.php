<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\FormBlock;

use ICanBoogie\Event;

/**
 * Base class for the alter events of the {@link FormBlock} class.
 */
abstract class AlterEvent extends Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Reference to the attributes for the {@link Form} element.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the actions for the {@link Form} element.
	 *
	 * @var array
	 */
	public $actions;

	/**
	 * Reference to the children for the {@link Form} element.
	 *
	 * @var array
	 */
	public $children;

	/**
	 * Reference to the values for the {@link Form} element.
	 *
	 * @var array
	 */
	public $values;
}
