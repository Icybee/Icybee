<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Nodes;

class Delete extends \Icybee\Operation\ActiveRecord\Delete
{
	/**
	 * Overrides the method to create a nicer log entry.
	 *
	 * @see ICanBoogie\Operation\ActiveRecord.Delete::process()
	 */
	protected function process()
	{
		$title = $this->record->title;
		$rc = parent::process();

		if ($rc)
		{
			wd_log_done
			(
				'%title has been deleted from %module.', array
				(
					'%title' => wd_shorten($title), '%module' => $this->module->title
				),

				'delete'
			);
		}

		return $rc;
	}
}