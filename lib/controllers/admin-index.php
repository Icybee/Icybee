<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\AuthenticationRequired;
use ICanBoogie\HTTP\Request;

class AdminIndexController extends \ICanBoogie\Routing\Controller
{
	public function __invoke(Request $request)
	{
		global $core;

		if ($core->user->is_guest)
		{
			throw new AuthenticationRequired();
		}

		return new DocumentDecorator(new AdminDecorator(''));
	}
}