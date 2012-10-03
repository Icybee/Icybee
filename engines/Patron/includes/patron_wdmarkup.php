<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Exception;

class patron_WdMarkup
{
	protected $constructor;
	protected $model;

	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		throw new Exception('The __invoke method must be overrode');
	}

	protected function publish(Patron\Engine $patron, $template, $entries=null, array $options=array())
	{
		return $patron($template, $entries, $options);
	}
}