<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Contents;

/**
 * Excludes a record from the home page.
 */
class HomeExclude extends HomeInclude
{
	protected function process()
	{
		$record = $this->record;
		$record->is_home_excluded = true;
		$record->save();

		wd_log_done('%title is now excluded from the home page', array('%title' => $record->title));

		return true;
	}
}