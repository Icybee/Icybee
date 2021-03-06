<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Module\ConfigOperation;

use ICanBoogie\Event;
use ICanBoogie\HTTP\Request;

use Icybee\Operation\Module\ConfigOperation;

/**
 * Event class for the `Icybee\Operation\Module\ConfigOperation::properties` event.
 *
 * @property-read Request $request
 */
class PropertiesEvent extends Event
{
	/**
	 * The HTTP request.
	 *
	 * @var \ICanBoogie\HTTP\Request
	 */
	private $request;

	protected function get_request()
	{
		return $this->request;
	}

	/**
	 * Reference to the config properties.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * The event is constructed with the type `properties`.
	 *
	 * @param \Icybee\Operation\Module\ConfigOperation $target
	 * @param Request $request
	 * @param array $properties
	 */
	public function __construct(ConfigOperation $target, Request $request, array &$properties)
	{
		$this->request = $request;
		$this->properties = &$properties;

		parent::__construct($target, 'properties');
	}
}
