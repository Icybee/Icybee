<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element\ActionBarSearch;

use ICanBoogie\Event;

use Icybee\Element\ActionBarSearch;

/**
 * Event class for the `Icybee\Element\ActionBarSearch::alter_inner_html` event.
 */
class AlterInnerHTMLEvent extends Event
{
	/**
	 * Reference to the rendered inner HTML of the element.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `alter_inner_html`.
	 *
	 * @param ActionBarSearch $target
	 * @param array $payload
	 */
	public function __construct(ActionBarSearch $target, array $payload)
	{
		parent::__construct($target, 'alter_inner_html', $payload);
	}
}
