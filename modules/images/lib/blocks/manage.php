<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Images;

use ICanBoogie\ActiveRecord\Query;

class ManageBlock extends \Icybee\Modules\Files\ManageBlock
{
	public function __construct(Module $module, array $attributes)
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_COLUMNS_ORDER => array
				(
					'title', 'is_online', 'uid', 'surface', 'size', 'modified'
				)
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('../../public/slimbox.js');
		$document->css->add('../../public/slimbox.css');
		$document->js->add('manage.js');
		$document->css->add('manage.css');
	}

	protected function columns()
	{
		$columns = parent::columns() + array
		(
			'surface' => array
			(
				'label' => 'Dimensions',
				'class' => 'size pull-right'
			)
		);

		$columns['title']['class'] = 'thumbnail';

		return $columns;
	}

	protected function extend_column_surface(array $column, $id, array $fields)
	{
		if ($this->count < 10 && !$this->options['filters'])
		{
			return parent::extend_column($column, $id, $fields);
		}

		return array
		(
			'filters' => array
			(
				'options' => array
				(
					'=b' => 'Big',
					'=m' => 'Medium',
					'=s' => 'Small'
				)
			)
		)

		+ parent::extend_column($column, $id, $fields);
	}

	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['surface']))
		{
			$value = $modifiers['surface'];

			if (in_array($value, array('b', 'm', 's')))
			{
				$filters['surface'] = $value;
			}
			else
			{
				unset($filters['surface']);
			}
		}

		return $filters;
	}

	protected function alter_query(Query $query, array $filters)
	{
		$query = parent::alter_query($query, $filters);

		if (isset($filters['surface']))
		{
			list($avg, $max, $min) = $this->model->select('AVG(width * height), MAX(width * height), MIN(width * height)')->similar_site->one(\PDO::FETCH_NUM);

			$bounds = array
			(
				$min,
				round($avg - ($avg - $min) / 3),
				round($avg),
				round($avg + ($max - $avg) / 3),
				$max
			);

			switch ($filters['surface'])
			{
				case 'b': $query->where('width * height >= ?', $bounds[3]); break;
				case 'm': $query->where('width * height >= ? AND width * height < ?', $bounds[2], $bounds[3]); break;
				case 's': $query->where('width * height < ?', $bounds[2]); break;
			}
		}

		return $query;
	}

	/**
	 * Alters the range query to support the "surface" virtual property.
	 *
	 * @see Icybee\Manager::alter_range_query()
	 */
	protected function alter_range_query(Query $query, array $options)
	{
		if (isset($options['order']['surface']))
		{
			$query->order('(width * height) ' . ($options['order']['surface'] < 0 ? 'DESC' : ''));

			$options['order'] = array();
		}

		return parent::alter_range_query($query, $options);
	}

	protected function render_cell_title($record, $property)
	{
		$path = $record->path;
		$thumbnail_element = $record->thumbnail('$icon')->to_element();
		$thumbnail_element->add_class('icon');

		$rc  = '<a href="' . \ICanBoogie\escape($path) . '" rel="lightbox[]">';
		$rc .= $thumbnail_element;
		$rc .= '<input type="hidden" value="' . \ICanBoogie\escape($record->thumbnail('$popup')->url) . '" />';
		$rc .= '</a>';

		$rc .= parent::render_cell_title($record, $property);

		return $rc;
	}

	protected function render_cell_surface($record)
	{
		return $record->width . '&times;' . $record->height . '&nbsp;px';
	}
}