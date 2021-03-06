<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block;

use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

use Icybee\Module;

/**
 * Base class for configuration blocks.
 */
abstract class ConfigBlock extends FormBlock
{
	public function render()
	{
		parent::render();

		return $this;
	}

	protected function get_permission()
	{
		return $this->app->user->has_permission(Module::PERMISSION_ADMINISTER, $this->module);
	}

	protected function access_control()
	{
		if (!$this->permission)
		{
			throw new \Exception(\ICanBoogie\format("You don't have permission to access the config block of the %module module", array('module' => $this->module->title)));
		}
	}

	/**
	 * Add the operation name {@link Module::OPERATION_CONFIG}.
	 */
	protected function lazy_get_attributes()
	{
		return \ICanBoogie\array_merge_recursive(parent::lazy_get_attributes(), [

			Form::HIDDENS => [

				Operation::NAME => Module::OPERATION_CONFIG

			]
		]);
	}

	protected function alter_actions(array $actions, array $params)
	{
		return array_merge(parent::alter_actions($actions, $params), [

			'primary' => new Button('Save', [ 'class' => 'btn-primary', 'type' => 'submit', 'name' => false ])

		]);
	}

	protected function alter_values(array $values, array $params)
	{
		$values = parent::alter_values($values, $params);

		$iterator = new Form([ Element::CHILDREN => $this->children ]);
		$registry = $this->app->registry;
		$local = $this->app->site->metas;

		foreach ($iterator as $child)
		{
			$name = $child['name'];

			if (!$name)
			{
				continue;
			}

			$dotted_name = strtr($name, [ '[' => '.', ']' => '' ]);

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

		return $values;
	}
}
