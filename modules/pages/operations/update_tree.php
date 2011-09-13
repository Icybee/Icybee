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

class UpdateTree extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		return !empty($this->params['parents']);
	}

	protected function process()
	{
		$w = 0;
		$update = $this->module->model->prepare('UPDATE {self} SET `parentid` = ?, `weight` = ? WHERE `{primary}` = ? LIMIT 1');
		$parents = $this->params['parents'];

		foreach ($parents as $nid => $parentid)
		{
			// FIXME-20100429: cached entries are not updated here, we should flush the cache.

			$update->execute(array($parentid, $w++, $nid));
		}

		return true;
	}
}