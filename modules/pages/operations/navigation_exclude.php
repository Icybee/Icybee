<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Pages;

class NavigationExclude extends \ICanBoogie\Operation\Pages\NavigationInclude
{
	protected function process()
	{
		$record = $this->record;
		$record->is_navigation_excluded = true;
		$record->save();

		return true;
	}
}