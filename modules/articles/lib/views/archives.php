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

class ArchivesView extends \Icybee\Modules\Views\View
{
	/**
	 * Returns records grouped by month.
	 *
	 * @see Icybee\Modules\Views.View::provide()
	 */
	protected function provide($provider, &$context, array $conditions)
	{
		$records = parent::provide($provider, $context, $conditions);

		$by_month = array();

		foreach ($records as $record)
		{
			$date = substr($record->date, 0, 7) . '-01';
			$by_month[$date][] = $record;
		}

		return $by_month;
	}
}