<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element\Document;

use ICanBoogie\Event;
use Brickrouge\Document;

/**
 * Event class for the `Brickrouge\Document::render_title` event.
 */
class RenderTitleEvent extends Event
{
	/**
	 * HTML of the `TITLE` markup.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_title`.
	 *
	 * @param Document $target
	 * @param array $payload
	 */
	public function __construct(Document $target, array $payload)
	{
		parent::__construct($target, 'render_title', $payload);
	}
}
