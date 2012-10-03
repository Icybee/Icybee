<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
