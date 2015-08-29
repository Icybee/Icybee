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
 * Event class for the `Icybee\Operation\Module\ConfigOperation::properties:before` event.
 *
 * @property Request $request
 */
class BeforePropertiesEvent extends Event
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
	 * The event is constructed with the type `properties:before`.
	 *
	 * @param ConfigOperation $target
	 * @param Request $request
	 */
	public function __construct(ConfigOperation $target, Request $request)
	{
		$this->request = $request;

		parent::__construct($target, 'properties:before');
	}
}
