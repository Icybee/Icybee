<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

use Brickrouge\Popover;

class EditorOperation extends BaseOperation
{
	protected function process()
	{
		$collection = new Collection();
		$cache = $collection[$this->key];

		return (string) new Popover(array
		(
			Popover::INNER_HTML => (string) $cache->editor,
			Popover::ACTIONS => 'boolean',
			Popover::FIT_CONTENT => true,

			'class' => 'popover contrast'
		));
	}
}