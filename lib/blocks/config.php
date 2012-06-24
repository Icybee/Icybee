<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Exception;
use ICanBoogie\Operation;

use Brickrouge\Button;

/**
 * Base class for configuration blocks.
 */
class ConfigBlock extends FormBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct($module, $attributes);

		$this->read_config();
	}

	public function __toString()
	{
		try
		{
			$this->save();
		}
		catch (\Exception $e)
		{
			return \BrickRouge\render_exception($e);
		}

		return parent::__toString();
	}

	protected function get_permission()
	{
		global $core;

		return $core->user->has_permission(Module::PERMISSION_ADMINISTER, $this->module);
	}

	protected function access_control()
	{
		if (!$this->permission)
		{
			throw new Exception("You don't have permission to access the config block of the %module module", array('module' => $this->module->title));
		}
	}

	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				self::HIDDENS => array
				(
					Operation::NAME => Module::OPERATION_CONFIG
				)
			)
		);
	}

	protected function alter_actions(array $actions)
	{
		$actions = parent::alter_actions($actions);

		$actions[0] = new Button('Save', array('class' => 'btn-primary', 'type' => 'submit'));

		return $actions;
	}

	protected function read_config()
	{
		global $core;

		$registry = $core->registry;
		$local = $core->site->metas;
		$iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
		$values = array();

		foreach ($iterator as $element)
		{
			$name = $element['name'];

			if (!$name)
			{
				continue;
			}

			$dotted_name = strtr($name, array('[' => '.', ']' => ''));

			$value = null;

			if (strpos($dotted_name, 'local.') === 0)
			{
				$value = $local[substr($dotted_name, 6)];
			}
			else if (strpos($dotted_name, 'global.') === 0)
			{
				$value = $registry[substr($dotted_name, 7)];
			}
			else
			{
				// COMPAT

				$value = $registry[$dotted_name];
			}

			if ($value === null)
			{
				continue;
			}

			$values[$name] = $value;
		}

		$this[self::VALUES] = $this[self::VALUES] + $values;
	}
}