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

use Brickrouge\Document;
use ICanBoogie\Event;

/**
 * Event class for the `Brickrouge\Document::render_title:before` event.
 *
 * @todo-20130318: is `title` the only property of the payload ? there should be `page_title`,
 * `site_title` and `separator`. Or an array of parts with the `page` and `site` key.
 */
class BeforeRenderTitleEvent extends Event
{
	/**
	 * Reference of the title to render.
	 *
	 * @var string
	 */
	public $title;

	public $separator = ' – ';

	/**
	 * The event is constructed with the type `render_title:before`.
	 *
	 * @param Document $target
	 * @param array $payload
	 */
	public function __construct(Document $target, array $payload)
	{
		parent::__construct($target, 'render_title:before', $payload);
	}
}
