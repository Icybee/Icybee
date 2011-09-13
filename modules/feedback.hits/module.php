<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Feedback;

use Icybee\Manager;

class Hits extends \Icybee\Module
{
	const OPERATION_HIT = 'hit';

	protected function block_manage()
	{
		return new Manager\Feedback\Hits
		(
			$this
		);
	}
}