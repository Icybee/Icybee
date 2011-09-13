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

use ICanBoogie\Module;
use ICanBoogie\Operation;

class Copy extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE,
			self::CONTROL_RECORD => true
		)

		+ parent::__get_controls();
	}

	protected function process()
	{
		global $core;

		$record = $this->record;
		$key = $this->key;
		$title = $record->title;

		unset($record->nid);
		unset($record->is_online);
		unset($record->created);
		unset($record->modified);

		$record->uid = $core->user_id;
		$record->title .= ' (copie)';
		$record->slug .= '-copie';

		$contentsModel = $this->module->model('contents');
		$contents = $contentsModel->where(array('pageid' => $key))->all;

		$nid = $this->module->model->save((array) $record);

		if (!$nid)
		{
			wd_log_error('Unable to copy page %title (#:nid)', array('%title' => $title, ':nid' => $key));

			return;
		}

		wd_log_done('Page %title was copied to %copy', array('%title' => $title, '%copy' => $record->title));

		foreach ($contents as $record)
		{
			$record->pageid = $nid;
			$record = (array) $record;

			$contentsModel->insert
			(
				$record,

				array
				(
					'on duplicate' => $record
				)
			);
		}

		return array($key, $nid);
	}
}