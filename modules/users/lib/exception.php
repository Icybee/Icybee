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

use ICanBoogie\PermissionRequired;

class WebsiteAdminNotAccessible extends PermissionRequired
{
	public function __construct($message="You don't have permission to access the admin of this website.", $code=500, \Exception $previous=null)
	{
		parent::__construct($message, $code, $previous);
	}
}
