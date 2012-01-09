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
	/**
	 * Adds the "archives" view type and adds assets to the inherited "list" view type.
	 *
	 * @see ICanBoogie\Module.Contents::__get_views()
	 */
	protected function __get_views()
	{
		$assets = array
		(
			'css' => array
			(
				__DIR__ . '/public/page.css'
			)
		);

		return wd_array_merge_recursive
		(
			parent::__get_views(), array
			(
				'list' => array
				(
					'assets' => $assets
				),

				'archives' => array
				(
					'title' => "Archives des articles",
					'class' => 'Icybee\Views\Articles\Archives',
					'provider' => 'Icybee\Views\Contents\Provider',
					'assets' => $assets
				)
			)
		);
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
}