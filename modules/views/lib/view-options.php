<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

// TODO-20130201: a class to create view options

class ViewOptions
{
	const RENDERS_ONE = 1;
	const RENDERS_MANY = 2;
	const RENDERS_OTHER = 3;

	public $access_callback;
	public $assets;
	public $class;
	public $id;
	public $provider;
	public $renders;
	public $title;
}