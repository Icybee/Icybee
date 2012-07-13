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

class DeleteOperation extends \ICanBoogie\DeleteOperation
{
	protected function process()
	{
		$rc = parent::process();

		unset($core->vars['cached_sites']);

		return $rc;
	}
}