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

/**
 * A column for size properties.
 */
class SizeColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=[])
	{
		parent::__construct($manager, $id, $options + [

			'class' => 'measure',
			'default_order' => -1,
			'discreet' => true

		]);
	}

	public function render_cell($record)
	{
		$property = $this->id;

		return \ICanBoogie\I18n\format_size($record->$property);
	}
}
