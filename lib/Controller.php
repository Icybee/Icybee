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

use ICanBoogie\Binding\Routing\ControllerBindings as RoutingBindings;
use ICanBoogie\View\ControllerBindings as ViewBindings;

abstract class Controller extends \ICanBoogie\Routing\Controller
{
	use RoutingBindings, ViewBindings;
}
