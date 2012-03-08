<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents;

use ICanBoogie\ActiveRecord\Content;

/**
 * The "contents" module extends the "system.nodes" module by offrering a subtitle, a body
 * (with a customizable editor), an optional excerpt, a date and a new visibility option (home).
 */
class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_HOME_INCLUDE = 'home_include';
	const OPERATION_HOME_EXCLUDE = 'home_exclude';

	/**
	 * Overrites the "view", "list" and "home" views to provide different titles and providers.
	 *
	 * @see Icybee.Module::__get_views()
	 */
	protected function __get_views()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::__get_views(), array
			(
				'view' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				),

				'list' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				),

				'home' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				)
			)
		);
	}
}