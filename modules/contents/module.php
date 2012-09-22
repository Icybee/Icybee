<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

/**
 * The "contents" module extends the "nodes" module by offrering a subtitle, a body
 * (with a customizable editor), an optional excerpt, a date and a new visibility option (home).
 */
class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_HOME_INCLUDE = 'home_include';
	const OPERATION_HOME_EXCLUDE = 'home_exclude';

	/**
	 * Overrites the "view", "list" and "home" views to provide different titles and providers.
	 *
	 * @see ICanBoogie\Modules\Nodes.Module::get_views()
	 */
	protected function get_views()
	{
		$options = array
		(
			'assets' => array
			(
				'css' => array(__DIR__ . '/assets/page.css')
			),

			'provider' => __NAMESPACE__ . '\ViewProvider'
		);

		return \ICanBoogie\array_merge_recursive
		(
			parent::get_views(), array
			(
				'view' => $options,
				'list' => $options,
				'home' => $options
			)
		);
	}
}