<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Feedback\Hits;

use ICanBoogie\Operation;

class Hit extends Operation
{
	protected function validate(\ICanboogie\Errors $errors)
	{
		if (!$this->key)
		{
			return false;
		}

		return true;
	}

	protected function process()
	{
		$nid = $this->key;

		$this->module->model->execute
		(
			'INSERT {self} (`nid`, `hits`, `first`, `last`) VALUES (?, 1, NOW(), NOW())
			ON DUPLICATE KEY UPDATE `hits` = `hits` + 1, `last` = NOW()', array
			(
				$nid
			)
		);

		return true;
	}
}