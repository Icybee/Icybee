<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\News;

class Module extends \Icybee\Modules\Contents\Module
{
	protected function get_views()
	{
		$assets = array
		(
			'assets' => array('css' => array(__DIR__ . '/public/page.css'))
		);

		return \ICanBoogie\array_merge_recursive
		(
			parent::get_views(), array
			(
				'view' => $assets,
				'list' => $assets,
				'home' => $assets
			)
		);
	}
}