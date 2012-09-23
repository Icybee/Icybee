<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

class ManageBlock extends \Icybee\Modules\Nodes\ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_COLUMNS_ORDER => array('title', 'is_online', 'modelid', 'uid', 'modified')
			)
		);
	}

	protected function columns()
	{
		return parent::columns() + array
		(
			'modelid' => array
			(

			)
		);
	}

	static protected $modelid_models;

	protected function render_cell_modelid($record, $property)
	{
		global $core;

		if (empty(self::$modelid_models))
		{
			self::$modelid_models = $core->configs->synthesize('formmodels', 'merge');
		}

		$modelid = $record->$property;
		$label = $modelid;

		if (isset(self::$modelid_models[$modelid]))
		{
			$label = $this->t->__invoke(self::$modelid_models[$modelid]['title']);
		}

		return $this->render_filter_cell($record, $property, $label);
	}
}