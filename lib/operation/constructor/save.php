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

class Save extends \Icybee\SaveOperation
{
	/**
	 * Adds the constructor id to the properties.
	 *
	 * @see ICanBoogie\Operation\ActiveRecord.Save::get_properties()
	 */
	protected function get_properties()
	{
		$properties = parent::get_properties();

		$properties['constructor'] = (string) $this->module;

		return $properties;
	}
}