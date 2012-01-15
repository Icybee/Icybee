<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users;

use ICanBoogie\ActiveRecord\User;

use BrickRouge\Element;

class Manager extends \WdManager
{
	public function __construct($module, array $tags=array())
	{
		parent::__construct
		(
			$module, $tags + array
			(
				self::T_KEY => User::UID
			)
		);
	}

	protected static function add_assets(\BrickRouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('assets/admin.css');
		$document->js->add('assets/manager.js');
	}

	protected function columns()
	{
		return array
		(
			User::USERNAME => array
			(
				'label' => 'Username',
				'ordering' => true
			),

			User::EMAIL => array
			(
				'label' => 'E-Mail'
			),

			'role' => array
			(
				'label' => 'Role',
				'orderable' => false
			),

			User::CREATED => array
			(
				'class' => 'date'
			),

			User::LASTCONNECTION => array
			(
				'class' => 'date'
			),

			User::IS_ACTIVATED => array
			(
				'label' => 'Activated',
				'class' => 'is_activated',
				'orderable' => false
			)
		);
	}

	protected function jobs()
	{
		global $core;

		// TODO: use parent::jobs()

		$jobs = array
		(
			Module::OPERATION_ACTIVATE => t('activate.operation.title'),
			Module::OPERATION_DEACTIVATE => t('deactivate.operation.title')
		);

		return $jobs;
	}

	protected function render_cell_username($record)
	{
		$label = $record->username;
		$name = $record->name;

		if ($label != $name)
		{
			$label .= ' <small>(' . $name . ')</small>';
		}

		return parent::modify_code($label, $record->uid, $this);
	}

	protected function render_cell_role($record)
	{
		if ($record->uid == 1)
		{
			return '<em>Admin</em>';
		}
		else if ($record->roles)
		{
			$label = '';

			foreach ($record->roles as $role)
			{
				if ($role->rid == 2)
				{
					continue;
				}

				$label .= ', ' . $role->name;
			}

			$label = substr($label, 2);
		}

		return $label;
	}

	protected function render_cell_created($record, $property)
	{
		return $this->render_cell_datetime($record, $property);
	}

	protected function render_cell_lastconnection($record, $property)
	{
		if (!((int) $record->$property))
		{
			return '<em class="small">Never connected</em>';
		}

		return $this->render_cell_datetime($record, $property);
	}

	protected function render_cell_is_activated($record)
	{
		if ($record->is_admin)
		{
			return;
		}

		return new Element
		(
			'label', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							'value' => $record->uid,
							'checked' => ($record->is_activated != 0)
						)
					)
				),

				'class' => 'checkbox-wrapper circle'
			)
		);
	}
}