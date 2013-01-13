<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\I18n\FormattedString;

class DeleteOperation extends \Icybee\DeleteOperation
{
	/**
	 * Overrides the method to create a nicer log entry.
	 *
	 * @see ICanBoogie\DeleteOperation::process()
	 */
	protected function process()
	{
		$title = $this->record->title;
		$rc = parent::process();

		if ($rc)
		{
			$this->response->message = new FormattedString
			(
				'%title has been deleted from %module.', array
				(
					'title' => \ICanBoogie\shorten($title),
					'module' => $this->module->title
				)
			);
		}

		return $rc;
	}
}