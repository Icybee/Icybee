<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BlueTihi\Context;

use ICanBoogie\Event;

use BlueTihi\Context;

class LoadedNodesEvent extends Event
{
	/**
	 * Loaded nodes.
	 *
	 * @var array
	 */
	public $nodes;

	/**
	 * The event is constructed with the type `loaded_nodes`.
	 *
	 * Warning: A node array must be provided instead of an event payload.
	 *
	 * @param Context $target
	 * @param array $nodes
	 */
	public function __construct(Context $target, array $nodes)
	{
		parent::__construct($target, 'loaded_nodes', [ 'nodes' => $nodes ]);
	}
}
