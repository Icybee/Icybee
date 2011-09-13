<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Sites;

class Delete extends \ICanBoogie\Operation\ActiveRecord\Delete
{
	protected function process()
	{
		$rc = parent::process();

		$this->module->update_cache();

		return $rc;
	}
}