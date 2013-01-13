<?php

namespace Icybee\Element\Manager;

class Column extends \ICanBoogie\Object
{
	public $label = "Spam caught";
	public $class = 'measure';
	public $filters;
	public $filtering = false;
	public $reset;
	public $orderable = false;
	public $order;
	public $default_order = 1;
	public $discreet = true;
	public $cell_render;

 	public function render_cell($record, $property)
 	{
 		return $this->cell_render($record, $property);
 	}
}

class CaughtCell extends CellRenderer
{
	public function __invoke($record, $property)
 	{
 		$count = $this->model->select('`count`')->filter_by_formid($record->nid)->rc;
 		$label = I18n\t(':count spam caught', array(':count' => $count));

 		if (!$count)
 		{
 			return '<em class="small">' . $label . '</em>';
 		}

 		return $label;
 	}
}