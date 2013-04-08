<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use Brickrouge\Group;

use Icybee\Modules\Users\User;

use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

/**
 * A block to edit users.
 */
class EditBlock extends \Icybee\EditBlock
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->js->add(DIR . 'public/admin.js');
	}

	protected function get_permission()
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

		return parent::get_permission();
	}

	protected function get_attributes()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_attributes(), array
			(
				Element::GROUPS => array
				(
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

	protected function get_children()
	{
		global $core;

		$values = $this->values;

		#
		# permissions
		#

		$user = $core->user;

		$permission = $this->permission;

		$uid = $values[User::UID];

		#
		# display options
		#

		$display_options = array
		(
			'<username>'
		);

		if ($values[User::USERNAME])
		{
			$display_options[0] = $values[User::USERNAME];
		}

		$firstname = $values[User::FIRSTNAME];

		if ($firstname)
		{
			$display_options[1] = $firstname;
		}

		$lastname = $values[User::LASTNAME];

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

		$role_el = $this->create_control_role();

		#
		# languages
		#

		$languages = $core->locale->conventions['localeDisplayNames']['languages'];

		uasort($languages, 'ICanBoogie\unaccent_compare_ci');

		#
		# restricted sites
		#

		$restricted_sites_el = $this->create_control_sites();

		$administer = $user->has_permission(Module::PERMISSION_MANAGE, $this->module);

		return array_merge
		(
			parent::get_children(), array
			(
				#
				# name group
				#

				User::FIRSTNAME => new Text
				(
					array
					(
						Group::LABEL => 'firstname'
					)
				),

				User::LASTNAME => new Text
				(
					array
					(
						Group::LABEL => 'lastname'
					)
				),

				User::NICKNAME => new Text
				(
					array
					(
						Group::LABEL => 'Nickname'
					)
				),

				User::USERNAME => $administer ? new Text
				(
					array
					(
						Group::LABEL => 'username',
						Element::REQUIRED => true
					)
				) : null,

				User::NAME_AS => new Element
				(
					'select', array
					(
						Group::LABEL => 'name_as',
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
						Group::LABEL => 'email',
						Element::GROUP => 'connection',
						Element::REQUIRED => true,

						'autocomplete' => 'off'
					)
				),

				User::PASSWORD => new Text
				(
					array
					(
						Element::LABEL => 'password',
						Element::LABEL_POSITION => 'above',
						Element::DESCRIPTION => 'password_' . ($uid ? 'update' : 'new'),
						Element::GROUP => 'connection',

						'autocomplete' => 'off',
						'type' => 'password',
						'value' => ''
					)
				),

				User::PASSWORD . '-verify' => new Text
				(
					array
					(
						Element::LABEL => 'password_confirm',
						Element::LABEL_POSITION => 'above',
						Element::DESCRIPTION => 'password_confirm',
						Element::GROUP => 'connection',

						'autocomplete' => 'off',
						'type' => 'password',
						'value' => ''
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
						Group::LABEL => 'language',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => 'language',
						Element::OPTIONS => array(null => '') + $languages
					)
				),

				'timezone' => new Widget\TimeZone
				(
					array
					(
						Group::LABEL => 'timezone',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => "Si la zone horaire n'est pas définie celle
						du site sera utilisée à la place."
					)
				),

				User::RESTRICTED_SITES => $restricted_sites_el
			)
		);
	}

	protected function alter_actions(array $actions, array $params)
	{
		global $core;

		$actions = parent::alter_actions($actions, $params);

		$user = $core->user;
		$record = $this->record;

		if ($record && $record->uid == $user->uid && !$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			unset($actions[\Icybee\SaveOperation::MODE]);
		}

		return $actions;
	}

	protected function create_control_role()
	{
		global $core;

		$user = $core->user;
		$uid = $this->values[User::UID];

		if ($uid == 1 || !$user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			return;
		}

		$rid = array
		(
			2 => true
		);

		if ($uid)
		{
			$record = $this->module->model[$uid];

			foreach ($record->roles as $role)
			{
				$rid[$role->rid] = true;
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
				'value' => $rid
			)
		);
	}

	protected function create_control_sites()
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