<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

class ProfileController extends \Icybee\BlockController
{
	protected function control_permission($permission)
	{
		global $core;

		$user = $core->user;

		if ($user->is_guest)
		{
			return false;
		}

		return $user->has_permission('modify own profile');
	}
}