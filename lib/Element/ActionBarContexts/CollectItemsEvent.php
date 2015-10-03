<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element\ActionBarContexts;

use ICanBoogie\Event;

use Icybee\Element\ActionBarContexts;

/**
 * Event class for the `Icybee\Element\ActionBarContexts::collect_items` event.
 */
class CollectItemsEvent extends Event
{
	/**
	 * Reference to the rendered inner HTML of the element.
	 *
	 * @var string
	 */
	public $items;

	/**
	 * The event is constructed with the type `collect_items`.
	 *
	 * @param ActionBarContexts $target
	 * @param array $items Reference to the contexts items.
	 */
	public function __construct(ActionBarContexts $target, array &$items)
	{
		$this->items = &$items;

		parent::__construct($target, 'collect_items');
	}
}
