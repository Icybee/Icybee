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

class Export extends \ICanBoogie\Operation\Nodes\Export
{
	protected function process()
	{
		$records = parent::process();

		$keys = array_keys($records);

		$contents = $this->module->model('contents')->where(array('pageid' => $keys))->all(\PDO::FETCH_OBJ);

		foreach ($contents as $content)
		{
			$records[$content->pageid]->contents[$content->contentid] = $content;

			unset($content->pageid);
			unset($content->contentid);
		}

		return $records;
	}
}