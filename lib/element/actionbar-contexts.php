<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

class ActionbarContexts extends Element
{
	public function __construct(array $attributes=[])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-contexts' ]);
	}

	protected function render_inner_html()
	{
		$items = array();

		new ActionbarContexts\CollectItemsEvent($this, $items);

		$html = implode($items);

		if (empty($html))
		{
			throw new ElementIsEmpty;
		}

		return $html;
	}
}

namespace Icybee\Element\ActionbarContexts;

/**
 * Event class for the `Icybee\Element\ActionbarContexts::collect_items` event.
 */
class CollectItemsEvent extends \ICanBoogie\Event
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
	 * @param (\Icybee\Element\ActionbarContexts $target
	 * @param array $items Reference to the contexts items.
	 */
	public function __construct(\Icybee\Element\ActionbarContexts $target, array &$items)
	{
		$this->items = &$items;

		parent::__construct($target, 'collect_items');
	}
}