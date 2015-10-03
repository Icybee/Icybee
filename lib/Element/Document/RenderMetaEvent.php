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
 * Event class for the `Brickrouge\Document::render_meta` event.
 */
class RenderMetaEvent extends Event
{
	/**
	 * Reference to the rendered HTML.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_meta`.
	 *
	 * @param Document $target
	 * @param string $html
	 */
	public function __construct(Document $target, &$html)
	{
		$this->html = &$html;

		parent::__construct($target, 'render_meta');
	}
}
