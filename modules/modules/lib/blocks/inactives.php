<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Modules;

use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

use Icybee\Element\ActionbarToolbar;

class InactivesBlock extends ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				'class' => 'form-primary block--modules-manage block--modules-disabled'
			)
		);

		$this->hiddens[Operation::NAME] = Module::OPERATION_ACTIVATE;
	}

	protected function get_columns()
	{
		$columns = parent::get_columns();

		unset($columns['install']);

		return $columns;
	}

	protected function get_descriptors()
	{
		global $core;

		return $core->modules->disabled_modules_descriptors;
	}

	protected function render_cell_key(array $descriptor, $module_id)
	{
		global $core;

		$disabled = false;

		$extends = $descriptor[Module::T_EXTENDS];

		if ($extends && !isset($core->modules[$extends]))
		{
			$disabled = true;
		}

		foreach ($descriptor[Module::T_REQUIRES] as $required_id => $required_version)
		{
			if (!isset($core->modules[$required_id]))
			{
				$disabled = true;

				continue;
			}

			// TODO-20120403: check dependency version
		}

		return new Element
		(
			Element::TYPE_CHECKBOX, array
			(
				'name' => Operation::KEY . '[' . $module_id . ']',
				'disabled' => $disabled
			)
		);
	}

	protected function attach_buttons()
	{
		\ICanBoogie\Event\attach
		(
			function(ActionbarToolbar\CollectEvent $event, ActionbarToolbar $target)
			{
				$event->buttons[] = new Button
				(
					'Enable selected modules', array
					(
						'class' => 'btn-primary btn-danger',
						'type' => 'submit',
						'data-target' => '.form-primary'
					)
				);
			}
		);
	}
}