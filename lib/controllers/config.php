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

use ICanBoogie\HTTP\Request;

class ConfigController extends \ICanBoogie\Controller
{
	public function __invoke(Request $request)
	{
		global $core;

		var_dump($this->route);

		$block = $core->modules['dashboard']->getBlock('config');
		$decorator = (string) new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($block));

		return $decorator;
	}
}
