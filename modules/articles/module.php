<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\ActiveRecord\Content;
use ICanBoogie\ActiveRecord\Query;
use BrickRouge;
use BrickRouge\Form;
use BrickRouge\Element;

class Articles extends Contents
{
	protected function block_edit(array $properties, $permission)
	{
		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::T_CHILDREN => array
				(
					Content::DATE => new BrickRouge\DateTime
					(
						array
						(
							Form::T_LABEL => 'Date',

							Element::T_REQUIRED => true,
							Element::T_DEFAULT => date('Y-m-d H:i:s')
						)
					)
				)
			)
		);
	}

	protected function provide_view_archives(Query $query)
	{
		$records = $query->own->visible->order('date DESC')->all;

		$by_month = array();

		foreach ($records as $record)
		{
			$date = substr($record->date, 0, 7) . '-01';
			$by_month[$date][] = $record;
		}

		return $by_month;
	}
}