<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Journal;

class Entry extends \ICanBoogie\ActiveRecord
{
	const SEVERITY = 'severity';
	const SEVERITY_DEBUG = 0;
	const SEVERITY_INFO = 1;
	const SEVERITY_WARNING = 2;
	const SEVERITY_DANGER = 3;

	public $id;
	public $siteid;
	public $uid;
	public $type;
	public $class;
	public $message;
	public $variables;
	public $severity;
	public $link;
	public $location;
	public $referer;
	public $timestamp;
}