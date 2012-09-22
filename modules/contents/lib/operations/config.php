<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

/**
 * The class doesn't do a thing but make config events more accurate because one can listen to the
 * configuration of a "contents" type module.
 */
class ConfigOperation extends \ICanBoogie\Modules\Nodes\ConfigOperation
{
	protected function get_properties()
	{
		$properties = parent::get_properties();

		$properties['local'] += array
		(
			"{$this->module->flat_id}.use_multi_editor" => false
		);

		return $properties;
	}
}