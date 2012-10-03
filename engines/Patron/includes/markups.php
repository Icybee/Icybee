<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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