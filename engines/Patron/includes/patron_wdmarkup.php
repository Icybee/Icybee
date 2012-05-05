<?php

/**
 * This file is part of the WdPatron software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.weirdog.com/wdpatron/
 * @copyright Copyright (c) 2007-2010 Olivier Laviale
 * @license http://www.weirdog.com/wdpatron/license/
 */

use ICanBoogie\Exception;

class patron_WdMarkup
{
	protected $constructor;
	protected $model;

	public function __invoke(array $args, WdPatron $patron, $template)
	{
		throw new Exception('The __invoke method must be overrode');
	}

	protected function publish(WdPatron $patron, $template, $entries=null, array $options=array())
	{
		return $patron($template, $entries, $options);
	}
}