<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Images;

class SaveOperation extends \Icybee\Modules\Files\SaveOperation
{
	protected $accept = array
	(
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpg' => 'image/jpeg'
	);
}