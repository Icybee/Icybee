<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Dashboard;

class BlockController extends \Icybee\BlockController
{
	protected function control_permission($permission)
	{
		global $core;

		if ($core->user->is_guest)
		{
			return false;
		}

		return true;
	}
}
