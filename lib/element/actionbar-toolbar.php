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

/**
 * A toolbar of the action bar.
 *
 * The `alter_buttons` event is fired to alter buttons before the inner HTML of the toolbar is
 * rendered.
 */
class ActionbarToolbar extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-toolbar btn-toolbar'));
	}

	protected function render_inner_html()
	{
		$buttons = $this->collect();

		new ActionbarToolbar\CollectEvent($this, array('buttons' => &$buttons));

		if (empty($buttons))
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		return implode($buttons);
	}

	protected function collect()
	{
		return array();
	}
}

namespace Icybee\Element\ActionbarToolbar;

/**
 * Event class for the `Icybee\Element\ActionbarToolbar::collect` event.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the button array to alter.
	 *
	 * @var array
	 */
	public $buttons;

	/**
	 * The event is constructed with the type `collect`.
	 *
	 * @param \Icybee\Element\ActionbarToolbar $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Element\ActionbarToolbar $target, array $payload)
	{
		parent::__construct($target, 'collect', $payload);
	}
}