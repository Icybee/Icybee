<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function wd_entities($str, $charset=ICanBoogie\CHARSET)
{
	return htmlspecialchars($str, ENT_COMPAT, $charset);
}

function wd_entities_all($str, $charset=ICanBoogie\CHARSET)
{
	return htmlentities($str, ENT_COMPAT, $charset);
}
