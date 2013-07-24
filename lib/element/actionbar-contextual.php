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

class ActionbarContextual extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-contextual'));
	}

	protected function render_inner_html()
	{
		$items = array();

		new ActionbarContextual\CollectItemsEvent($this, $items);

		$html = implode($items);

		if (empty($html))
		{
			throw new ElementIsEmpty;
		}

		return $html;
	}
}

namespace Icybee\Element\ActionbarContextual;

/**
 * Event class for the `Icybee\Element\ActionbarContextual::collect_items` event.
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
	 * @param \Icybee\Element\ActionbarSearch $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Element\ActionbarContextual $target, array &$items)
	{
		$this->items = &$items;

		parent::__construct($target, 'collect_items');
	}
}