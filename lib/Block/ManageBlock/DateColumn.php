<?php

namespace Icybee\Block\ManageBlock;

/**
 * Representation of a _date_ column.
 */
class DateColumn extends DateTimeColumn
{
	protected function render_cell_time($date, $property)
	{
		return;
	}
}
