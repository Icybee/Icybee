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

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		return parent::alter_query_with_filter($query, $filter_value)
		->where(array($this->id => $filter_value));
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
	 * @return \Brickrouge\Element
	 */
	public function __invoke($record, $property)
	{
		return new Element
		(
			'label', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							'value' => $record->{ $this->column->manager->primary_key },
							'checked' => ($record->$property != 0),
							'data-property' => $property
						)
					)
				),

				'class' => 'checkbox-wrapper circle'
			)
		);
	}
}