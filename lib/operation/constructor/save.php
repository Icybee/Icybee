<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Constructor;

use Icybee\Binding\ObjectBindings;

class Save extends \ICanBoogie\SaveOperation // FIXME-20130501: is this still used ?
{
	use ObjectBindings;

	/**
	 * Adds the constructor id to the properties.
	 */
	protected function lazy_get_properties()
	{
		$properties = parent::lazy_get_properties();

		$properties['constructor'] = (string) $this->module;

		return $properties;
	}
}
