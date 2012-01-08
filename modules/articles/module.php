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
	protected function __get_views()
	{
		$views = parent::__get_views() + array
		(
			'archives' => array
			(
				'title' => "Archives des articles",
				'class' => 'Icybee\Views\Articles\Archives',
				'provider' => 'Icybee\Views\Contents\Provider',
				'assets' => array
				(
					'css' => array
					(
						__DIR__ . '/public/page.css'
					)
				)
			)
		);

		$views['list']['assets']['css'][] = __DIR__ . '/public/page.css';

		return $views;
	}

	protected function block_edit(array $properties, $permission)
	{
		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::CHILDREN => array
				(
					Content::DATE => new BrickRouge\DateTime
					(
						array
						(
							Form::LABEL => 'Date',

							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => date('Y-m-d H:i:s')
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