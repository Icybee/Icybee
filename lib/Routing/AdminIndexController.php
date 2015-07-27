<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Routing;

use ICanBoogie\AuthenticationRequired;
use ICanBoogie\HTTP\Request;
use Icybee\AdminDecorator;
use Icybee\Controller;
use Icybee\DocumentDecorator;

/**
 * @property \Icybee\Modules\Users\User $user
 */
class AdminIndexController extends AdminController
{
	protected function action(Request $request)
	{
		if ($this->user->is_guest)
		{
			throw new AuthenticationRequired;
		}

		return new DocumentDecorator(new AdminDecorator(''));
	}
}
