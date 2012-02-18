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

class Module extends \Icybee\Module
{
	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'url', 'language', 'timezone', 'status')
			)
		);
	}
}