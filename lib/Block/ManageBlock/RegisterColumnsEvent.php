<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use ICanBoogie\Event;
use Icybee\Block\ManageBlock;

/**
 * Event class for the `Icybee\Block\ManageBlock::register_columns` event.
 */
class RegisterColumnsEvent extends Event
{
	/**
	 * Reference to the columns of the element.
	 *
	 * @var array[string]array
	 */
	public $columns;

	/**
	 * The event is constructed with the type `register_columns`.
	 *
	 * @param \Icybee\Block\ManageBlock $target
	 * @param array $columns Reference to the columns of the element.
	 */
	public function __construct(ManageBlock $target, array &$columns)
	{
		$this->columns = &$columns;

		parent::__construct($target, 'register_columns');
	}

	public function add(Column $column, $weight=null)
	{
		if ($weight)
		{
			list($position, $relative) = explode(':', $weight) + [ 'before' ];

			$this->columns = \ICanBoogie\array_insert($this->columns, $relative, $column, $column->id, $position == 'after');
		}
		else
		{
			$this->columns[$column->id] = $column;
		}
	}
}
