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

use Icybee\WrappedCheckbox;

/**
 * Representation of a _boolean_ column.
 */
class BooleanColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=[])
	{
		parent::__construct($manager, $id, $options + [

			'title' => null,
			'class' => 'cell-boolean',
			'discreet' => false,
			'filters' => [

				'options' => [

					'=1' => 'Yes',
					'=0' => 'No'

				]
			],

			'orderable' => false,
			'cell_renderer' => __NAMESPACE__ . '\BooleanCellRenderer'

		]);
	}

	public function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('boolean.column.js');
	}

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		return $query->and([ $this->id => $filter_value ]);
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
