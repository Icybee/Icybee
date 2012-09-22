<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Cache;

use Brickrouge\Popover;

/**
 * Returns the configuration editor.
 *
 * The editor is obtained through the `editor` property of the cache.
 */
class EditorOperation extends BaseOperation
{
	protected function process()
	{
		$cache = $this->collection[$this->key];

		return (string) new Popover(array
		(
			Popover::INNER_HTML => (string) $cache->editor,
			Popover::ACTIONS => 'boolean',
			Popover::FIT_CONTENT => true,

			'class' => 'popover contrast'
		));
	}
}