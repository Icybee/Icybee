<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Patron;

/**
 * Escape HTML special characters.
 *
 * HTML special characters are escaped using the htmlspecialchars() function with the
 * ENT_COMPAT flag.
 *
 * @param string $str The string to escape.
 * @param string $charset The charset of the string to escape. Defaults to ICanBoogie\CHARSET
 * (utf-8).
 *
 * @return string
 */
function escape($str, $charset=CHARSET)
{
	return htmlspecialchars($str, ENT_COMPAT, $charset);
}

/**
 * Escape all applicable characters to HTML entities.
 *
 * Applicable characters are escaped using the htmlentities() function with the ENT_COMPAT flag.
 *
 * @param string $str The string to escape.
 * @param string $charset The charset of the string to escape. Defaults to ICanBoogie\CHARSET
 * (utf-8).
 *
 * @return string
 */
function escape_all($str, $charset=CHARSET)
{
	return htmlentities($str, ENT_COMPAT, $charset);
}

function tr($str, $from, $to)
{
	return strtr($str, $from, $to);
}

/**
 * Initialize the parser and return the result of its publish method.
 *
 * @param $template
 * @return string The template published
 */
function render($template, $thisArg=null, array $options=array())
{
	static $engine;

	if (!$engine)
	{
		$engine = new Engine;
	}

// 	return WdPatron::get_singleton('Icybee')->__invoke($template, $bind, $options);
}