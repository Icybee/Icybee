<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_POST = 'post'; // TODO-20120922: rename as OPERATION_SEND
	const OPERATION_POST_ID = '#post-id'; // TODO-20120922: rename as OPERATION_SEND_FORM_KEY
	const OPERATION_DEFAULTS = 'defaults';
}