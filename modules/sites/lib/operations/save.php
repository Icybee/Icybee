<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Sites;

class SaveOperation extends \Icybee\SaveOperation
{
	protected function process()
	{
		global $core;

		$rc = parent::process();

		$record = $this->module->model[$rc['key']];

		$this->response->message = array
		(
			$rc['mode'] == 'update' ? '%title has been updated in %module.' : '%title has been created in %module.', array
			(
				'title' => \ICanBoogie\shorten($record->title),
				'module' => $this->module->title
			)
		);

		unset($core->vars['cached_sites']);

		return $rc;
	}
}