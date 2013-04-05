<?php

namespace Icybee\ManageBlock;

use ICanBoogie\ActiveRecord;

class Column
{
	protected $manager;
	protected $id;
	protected $options = array();

	public function __construct(\Icybee\ManageBlock $manager, $id, array $options)
	{
		$this->manager = $manager;
		$this->id = $id;

		$this->change
		(
			$options + array
			(
				'class' => null,

				'filters' => null,
				'filtering' => isset($manager->options['filters'][$id]),
				'reset' => "?$id=",

				'orderable' => false,
				'order' => null,
				'default_order' => null,

				'discreet' => false
			)
		);
	}

	public function change(array $options)
	{

	}

	public function alter_records(array $records)
	{

	}

	public function render_cell(ActiveRecord $record, $property)
	{
		return $record->$property;
	}
}

class ColumnTitle
{
	protected $manager;
	protected $id;

	public function __construct(\Icybee\ManageBlock $manager, $id)
	{
		$this->manager = $manager;
		$this->id = $id;
	}
}

class ColumnCell
{

}

class KeyColumnTitle
{

}