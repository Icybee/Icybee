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

class LoadedNodesEvent extends \ICanBoogie\Event
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
	 * @param \BlueTihi\Context $target
	 * @param array $nodes
	 */
	public function __construct(\BlueTihi\Context $target, array $nodes)
	{
		parent::__construct($target, 'loaded_nodes', [ 'nodes' => $nodes ]);
	}
}
