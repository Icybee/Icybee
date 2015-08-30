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

class AlterRenderedCellsEvent extends Event
{
	/**
	 * Reference to the rendered cells.
	 *
	 * @var array[string]string
	 */
	public $rendered_cells;

	/**
	 * The records used to render the cells.
	 *
	 * @var \ICanBoogie\ActiveRecord[]
	 */
	public $records;

	public function __construct(ManageBlock $target, array &$rendered_cells, array $records)
	{
		$this->rendered_cells = &$rendered_cells;
		$this->records = $records;

		parent::__construct($target, 'alter_rendered_cells');
	}
}
