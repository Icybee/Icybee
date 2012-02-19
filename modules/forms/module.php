<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Forms;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_POST = 'post';
	const OPERATION_POST_ID = '#post-id';
	const OPERATION_DEFAULTS = 'defaults';
}