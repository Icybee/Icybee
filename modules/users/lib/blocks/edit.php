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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

/**
 * A block to edit users.
 */
class EditBlock extends \Icybee\EditBlock
{
	protected function __get_permission()
	{
		global $core;

		$user = $core->user;

		if ($user->has_permission(Module::PERMISSION_MANAGE, $this->module))
		{
			return true;
		}
		else if ($user->uid == $this->record->uid && $user->has_permission('modify own profile'))
		{
			return true;
		}

		return parent::__get_permission();
	}

	protected function alter_attributes(array $attributes)
	{
		return wd_array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'contact' => array
					(
						'title' => 'Contact'
					),

					'connection' => array
					(
						'title' => 'Connection'
					),

					'advanced' => array
					(
						'title' => 'Advanced'
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$core->document->js->add('../../assets/admin.js');

		#
		# permissions
		#

		$user = $core->user;

		$permission = $this->permission;

		$uid = $properties[User::UID];

		#
		# display options
		#

		$display_options = array
		(
			'<username>'
		);

		if ($properties[User::USERNAME])
		{
			$display_options[0] = $properties[User::USERNAME];
		}

		$firstname = $properties[User::FIRSTNAME];

		if ($firstname)
		{
			$display_options[1] = $firstname;
		}

		$lastname = $properties[User::LASTNAME];

		if ($lastname)
		{
			$display_options[2] = $lastname;
		}

		if ($firstname && $lastname)
		{
			$display_options[3] = $firstname . ' ' . $lastname;
			$display_options[4] = $lastname . ' ' . $firstname;
		}

		#
		# roles
		#

		$role_el = $this->get_control_role($properties, $attributes);

		#
		# languages
		#

		$languages = $core->locale->conventions['localeDisplayNames']['languages'];

		uasort($languages, 'wd_unaccent_compare_ci');

		#
		# restricted sites
		#

		$restricted_sites_el = $this->get_control_sites();

		$administer = $user->has_permission(Module::PERMISSION_MANAGE, $this->module);

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				#
				# name group
				#

				User::FIRSTNAME => new Text
				(
					array
					(
						Form::LABEL => 'firstname',
						Element::GROUP => 'contact'
					)
				),

				User::LASTNAME => new Text
				(
					array
					(
						Form::LABEL => 'lastname',
						Element::GROUP => 'contact'
					)
				),

				User::USERNAME => $administer ? new Text
				(
					array
					(
						Form::LABEL => 'username',
						Element::GROUP => 'contact',
						Element::REQUIRED => true
					)
				) : null,

				User::DISPLAY => new Element
				(
					'select', array
					(
						Form::LABEL => 'display_as',
						Element::GROUP => 'contact',
						Element::OPTIONS => $display_options
					)
				),

				#
				# connection group
				#

				User::EMAIL => new Text
				(
					array
					(
						Form::LABEL => 'email',
						Element::GROUP => 'connection',
						Element::REQUIRED => true,

						'autocomplete' => 'off'
					)
				),

				new Element
				(
					'div', array
					(
						Element::GROUP => 'connection',
						Element::CHILDREN => array
						(
							'<div>',

							User::PASSWORD => new Text
							(
								array
								(
									Element::LABEL => 'password',
									Element::LABEL_POSITION => 'above',
									Element::DESCRIPTION => 'password_' . ($uid ? 'update' : 'new'),

									'autocomplete' => 'off',
									'type' => 'password',
									'value' => ''
								)
							),

							'</div><div>',

							User::PASSWORD . '-verify' => new Text
							(
								array
								(
									Element::LABEL => 'password_confirm',
									Element::LABEL_POSITION => 'above',
									Element::DESCRIPTION => 'password_confirm',

									'autocomplete' => 'off',
									'type' => 'password',
									'value' => ''
								)
							),

							'</div>'
						),

						'style' => 'column-count; -moz-column-count: 2; -webkit-column-count: 2'
					)
				),

				User::IS_ACTIVATED => ($uid == 1 || !$administer) ? null : new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'is_activated',
						Element::GROUP => 'connection',
						Element::DESCRIPTION => 'is_activated'
					)
				),

				User::ROLES => $role_el,

				User::LANGUAGE => new Element
				(
					'select', array
					(
						Form::LABEL => 'language',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => 'language',
						Element::OPTIONS => array(null => '') + $languages
					)
				),

				'timezone' => new Widget\TimeZone
				(
					array
					(
						Form::LABEL => 'timezone',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => "Si la zone horaire n'est pas définie celle
						du site sera utilisée à la place."
					)
				),

				User::RESTRICTED_SITES => $restricted_sites_el
			)
		);

		/* TODO: override save button

		if ($permission_modify_own_profile)
		{
			$rc[Element::CHILDREN]['#submit'] = new Button
			(
				'Save', array
				(
					Element::GROUP => 'save',
					'class' => 'save',
					'type' => 'submit'
				)
			);
		}

		*/
	}

	protected function alter_actions(array $actions)
	{
		global $core;

		$actions = parent::alter_actions($actions);

		$user = $core->user;
		$record = $this->record;

		if ($record && $record->uid == $user->uid && !$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			unset($actions[\Icybee\SaveOperation::MODE]);
		}

		return $actions;
	}

	protected function get_control_role(array &$properties, array &$attributes)
	{
		global $core;

		$user = $core->user;
		$uid = $properties[User::UID];

		if ($uid == 1 || !$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			return;
		}

		$properties_rid = array
		(
			2 => true
		);

		if ($uid)
		{
			$record = $this->module->model[$uid];

			foreach ($record->roles as $role)
			{
				$properties_rid[$role->rid] = true;
			}
		}

		return new Element
		(
			Element::TYPE_CHECKBOX_GROUP, array
			(
				Form::LABEL => 'roles',
				Element::GROUP => 'advanced',
				Element::OPTIONS => $core->models['users.roles']->select('rid, name')->where('rid != 1')->order('rid')->pairs,
				Element::OPTIONS_DISABLED => array(2 => true),
				Element::REQUIRED => true,
				Element::DESCRIPTION => 'roles',

				'class' => 'framed inputs-list sortable',
				'value' => $properties_rid
			)
		);
	}

	protected function get_control_sites()
	{
		global $core;

		$user = $core->user;

		if (!$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			return;
		}

		$value = array();

		if ($this->record)
		{
			$value = $this->record->restricted_sites_ids;

			if ($value)
			{
				$value = array_combine($value, array_fill(0, count($value), true));
			}
		}

		$options = $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs;

		if (!$options)
		{
			return;
		}

		return new Element
		(
			Element::TYPE_CHECKBOX_GROUP, array
			(
				Form::LABEL => 'siteid',
				Element::OPTIONS => $options,
				Element::GROUP => 'advanced',
				Element::DESCRIPTION => 'siteid',

				'class' => 'inputs-list',
				'value' => $value
			)
		);
	}
}