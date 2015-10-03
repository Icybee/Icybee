<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element\ActionBarToolbar;

use ICanBoogie\Event;
use Icybee\Element\ActionBarToolbar;

/**
 * Event class for the `Icybee\Element\ActionBarToolbar::collect` event.
 */
class CollectEvent extends Event
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
	 * @param ActionBarToolbar $target
	 * @param array $payload
	 */
	public function __construct(ActionBarToolbar $target, array $payload)
	{
		parent::__construct($target, 'collect', $payload);
	}
}
