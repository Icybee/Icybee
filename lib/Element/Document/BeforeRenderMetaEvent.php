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
 * Event class for the `Brickrouge\Document::render_meta:before` event.
 */
class BeforeRenderMetaEvent extends Event
{
	/**
	 * Reference to the HTTP equivalent array.
	 *
	 * @var array
	 */
	public $http_equiv;

	/**
	 * Reference to the meta array.
	 *
	 * The `og` array is used to define OpenGraph meta.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * The event is constructed with the type `render_meta:before`.
	 *
	 * @param Document $target
	 * @param string $http_equiv
	 * @param array $meta
	 */
	public function __construct(Document $target, &$http_equiv, &$meta)
	{
		$this->http_equiv = &$http_equiv;
		$this->meta = &$meta;

		parent::__construct($target, 'render_meta:before');
	}
}
