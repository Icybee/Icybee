<?php

namespace Icybee\Block\ManageBlock;

/**
 * Default cell renderer.
 */
class CellRenderer
{
	protected $column;

	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	public function __invoke($record, $property)
	{
		return \Brickrouge\escape($record->$property);
	}

	public function t($str, array $args=[], array $options=[])
	{
		return $this->column->t($str, $args, $options);
	}
}
