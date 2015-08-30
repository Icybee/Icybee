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

use Icybee\WrappedCheckbox;

/**
 * Renderer for the _boolean_ column cells.
 */
class BooleanCellRenderer extends CellRenderer
{
	/**
	 * Returns an decorated checkbox element.
	 *
	 * @return WrappedCheckbox
	 *
	 * @inheritdoc
	 */
	public function __invoke($record, $property)
	{
		return new WrappedCheckbox([

			'class' => 'wrapped-checkbox circle',
			'value' => $record->{ $this->column->manager->primary_key },
			'checked' => ($record->$property != 0),
			'data-property' => $property,
			'data-property-type' => 'boolean'

		]);
	}
}
