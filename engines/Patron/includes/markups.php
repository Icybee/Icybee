<?php

/**
 * This file is part of the WdPatron software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.weirdog.com/wdpatron/
 * @copyright Copyright (c) 2007-2010 Olivier Laviale
 * @license http://www.weirdog.com/wdpatron/license/
 */

// TODO-20101216: this class needs to die.

class patron_markups_WdHooks
{
	static protected function model($name=null)
	{
		global $core;

		return $core->models[$name];
	}

	static protected function parseSelect($select)
	{
		$where = array();
		$params = array();

		return array($where, $params);
	}
}