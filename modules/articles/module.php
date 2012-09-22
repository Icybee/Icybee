<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Articles;

class Module extends \ICanBoogie\Modules\Contents\Module
{
	/**
	 * Adds the "archives" view type and adds assets to the inherited "list" view type.
	 *
	 * @see ICanBoogie\Modules\Contents.Module::get_views()
	 */
	protected function get_views()
	{
		$assets = array
		(
			'css' => array(__DIR__ . '/public/page.css')
		);

		return \ICanBoogie\array_merge_recursive
		(
			parent::get_views(), array
			(
				'list' => array
				(
					'assets' => $assets
				),

				'archives' => array
				(
					'title' => "Archives des articles",
					'class' => __NAMESPACE__ . '\ArchivesView',
					'provider' => 'ICanBoogie\Modules\Contents\Provider',
					'assets' => $assets,
					'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
				)
			)
		);
	}
}