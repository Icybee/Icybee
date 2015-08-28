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

use Brickrouge\Document;

use Icybee\ManageBlock;

/**
 * Representation of a _boolean_ column.
 */
class BooleanColumn extends Column
{
	public function __construct(ManageBlock $manager, $id, array $options = [])
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
			'cell_renderer' => BooleanCellRenderer::class

		]);
	}

	public function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->js->add(__DIR__ . '/boolean.column.js');
	}

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		return $query->and([ $this->id => $filter_value ]);
	}
}
