<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes;

use ICanBoogie\ActiveRecord\Node;

use Brickrouge\Element;
use Brickrouge\Form;

use Brickrouge\Widget\TitleSlugCombo;

class EditBlock extends \Icybee\EditBlock
{
	/**
	 * Adds the "Visibility" group.
	 *
	 * The visibility group should be used to group controls related to the visibility of the
	 * record on the site e.g. online status, view exclusion, navigation exclusion...
	 *
	 * The visibility group is created with an initial weight of 400.
	 *
	 * @see Icybee.EditBlock::alter_attributes()
	 */
	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'visibility' => array
					(
						'title' => 'Visibility',
						'weight' => 400
					)
				)
			)
		);
	}

	/**
	 * Adds the "Title", "Online", "User" and "Site" elements.
	 *
	 * The "User" and "Site" elements are added according to the context.
	 *
	 * @see Icybee.EditBlock::alter_children()
	 */
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Node::TITLE => new TitleSlugCombo
				(
					array
					(
						Form::LABEL => 'title',
						Element::REQUIRED => true,
						TitleSlugCombo::T_NODEID => $properties[Node::NID],
						TitleSlugCombo::T_SLUG_NAME => 'slug'
					)
				),

				Node::UID => $this->get_control__user($properties, $attributes),
				Node::SITEID => $this->get_control__site($properties, $attributes),	// TODO-20100906: this should be added by the "sites" modules using the alter event.
				Node::IS_ONLINE => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'is_online',
						Element::DESCRIPTION => 'is_online',
						Element::GROUP => 'visibility'
					)
				)
			)
		);
	}

	/**
	 * Returns the control for the user of the node.
	 *
	 * @param array $properties
	 * @param array $attributes
	 *
	 * @return void|\Brickrouge\Element
	 */
	protected function get_control__user(array &$properties, array &$attributes)
	{
		global $core;

		if (!$core->user->has_permission(Module::PERMISSION_ADMINISTER, $this->module))
		{
			return;
		}

		$users = $core->models['users']->select('uid, username')->order('username')->pairs;

		if (count($users) < 2)
		{
			return;
		}

		return new Element
		(
			'select', array
			(
				Form::LABEL => 'User',
				Element::OPTIONS => array(null => '') + $users,
				Element::REQUIRED => true,
				Element::DEFAULT_VALUE => $core->user->uid,
				Element::GROUP => 'admin',
				Element::DESCRIPTION => 'user'
			)
		);
	}

	/**
	 * Returns control for the site the node belongs to.
	 *
	 * @param array $properties
	 * @param array $attributes
	 *
	 * @return void|\Brickrouge\Element
	 */
	protected function get_control__site(array &$properties, array &$attributes)
	{
		global $core;

		if (!$core->user->has_permission(Module::PERMISSION_MODIFY_BELONGING_SITE, $this->module))
		{
			return;
		}

		$sites = $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs;

		if (count($sites) < 2)
		{
			$attributes[self::HIDDENS][Node::SITEID] = $core->site_id;

			return;
		}

		return new Element
		(
			'select', array
			(
				Form::LABEL => 'siteid',
				Element::OPTIONS => array(null => '') + $sites,
				Element::GROUP => 'admin',
				Element::DESCRIPTION => 'siteid'
			)
		);
	}
}