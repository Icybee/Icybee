<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

class ExportOperation extends \ICanBoogie\Modules\Nodes\ExportOperation
{
	protected function process()
	{
		$records = parent::process();

		$keys = array_keys($records);

		$contents = $this->module->model('contents')->where(array('pageid' => $keys))->all(\PDO::FETCH_OBJ);

		foreach ($contents as $content)
		{
			$records[$content->pageid]->contents[$content->contentid] = $content->content;
			$records[$content->pageid]->editors[$content->contentid] = $content->editor;
		}

		return $records;
	}
}