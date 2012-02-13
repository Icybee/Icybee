<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Articles;

use ICanBoogie\ActiveRecord\Content;
use ICanBoogie\ActiveRecord\Query;
use Brickrouge;
use Brickrouge\Form;
use Brickrouge\Element;

class Module extends \ICanBoogie\Modules\Contents\Module
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
					'assets' => $assets,
					'renders' => \Icybee\Views\View::RENDERS_MANY
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
					Content::DATE => new Brickrouge\DateTime
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