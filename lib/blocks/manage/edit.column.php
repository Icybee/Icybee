<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\ManageBlock;

/**
 * Representation of a _boolean_ column.
 */
class EditColumn extends Column
{
	public function render_cell($record)
	{
		return new EditDecorator(\Brickrouge\escape($record->{ $this->id }), $record);
	}
}