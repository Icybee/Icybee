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

use ICanBoogie\ActiveRecord\Query;

use Brickrouge\Element;
use Icybee\WrappedCheckbox;

/**
 * Representation of a _boolean_ column.
 */
class BooleanColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'title' => null,
				'class' => 'cell-boolean',
				'discreet' => false,
				'filters' => array
				(
					'options' => array
					(
						'=1' => 'Yes',
						'=0' => 'No'
					)
				),

				'orderable' => false,
				'cell_renderer' => __NAMESPACE__ . '\BooleanCellRenderer'
			)
		);
	}

	public function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('boolean.column.js');
	}

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		return $query->and(array($this->id => $filter_value));
	}
}

/**
 * Renderer for the _boolean_ column cells.
 */
class BooleanCellRenderer extends CellRenderer
{
	/**
	 * Returns an decorated checkbox element.
	 *
	 * @return WrappedCheckbox
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