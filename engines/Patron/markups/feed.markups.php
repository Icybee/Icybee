<?php

/**
 * This file is part of the WdPatron software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.weirdog.com/wdpatron/
 * @copyright Copyright (c) 2007-2010 Olivier Laviale
 * @license http://www.weirdog.com/wdpatron/license/
 */

class patron_feed_WdMarkups
{
	static public function feed(array $args, Patron\Engine $patron, $template)
	{
		header('Content-Type: application/atom+xml');

		$rc = $patron($template);

		$rc = preg_replace('#(href|src)="/#', '$1="http://' . $_SERVER['SERVER_NAME'] .'/', $rc);

		return '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">' . $rc . '</feed>';
	}
}
