<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;

use Brickrouge\Element;

class ManageBlock extends \Icybee\Modules\Nodes\ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_COLUMNS_ORDER => array
				(
					'title', 'is_home_excluded', 'is_online', 'uid', 'date', 'modified'
				),

				self::T_ORDER_BY => array('date', 'desc')
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('manage.css');
		$document->js->add('manage.js');
	}

	protected function columns()
	{
		return parent::columns() + array
		(
			'date' => array
			(
				'class' => 'date'
			),

			'is_home_excluded' => array
			(
				'label' => null
			)
		);
	}

	/**
	 * Updates filters with the `is_home_excluded` filter.
	 *
	 * @see Icybee\Manager.Nodes::update_filters()
	 */
	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['is_home_excluded']))
		{
			$value = $modifiers['is_home_excluded'];

			if ($value === '' || $value === null)
			{
				unset($filters['is_home_excluded']);
			}
			else
			{
				$filters['is_home_excluded'] = !empty($value);
			}
		}

		return $filters;
	}

	/**
	 * Alters query with the `is_home_excluded` filter.
	 *
	 * @see Icybee\Manager.Nodes::alter_query()
	 */
	protected function alter_query(Query $query, array $filters)
	{
		if (isset($filters['is_home_excluded']))
		{
			$query->where('is_home_excluded = ?', $filters['is_home_excluded']);
		}

		return parent::alter_query($query, $filters);
	}

	/**
	 * Returns options for the `is_home_excluded` header cell.
	 *
	 * @param array $options
	 * @param string $id
	 *
	 * @return array
	 */
	protected function extend_column_is_home_excluded(array $options, $id, array $fields)
	{
		return array
		(
			'filters' => array
			(
				'options' => array
				(
					'=1' => "Exclus de l'accueil",
					'=0' => "Inclus à l'accueil"
				)
			),

			'sortable' => false
		)

		+ parent::extend_column($options, $id, $fields);
	}

	/**
	 * Renders a cell of the `is_home_excluded` column.
	 *
	 * @param ActiveRecord $record
	 * @param string $property
	 */
	protected function render_cell_is_home_excluded(ActiveRecord $record, $property)
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
							'value' => $record->nid,
							'checked' => ($record->$property != 0),
							'data-property' => $property
						)
					)
				),

				'title' => "Inclure ou exclure l'entrée de la page d'accueil",
				'class' => 'checkbox-wrapper home'
			)
		);
	}
}