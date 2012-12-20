<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use Icybee\Modules\Nodes\Node;

use Brickrouge\Element;
use Brickrouge\Form;

use Brickrouge\Widget\TitleSlugCombo;

/**
 * A block used to edit a node.
 */
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
	 * @see Icybee.EditBlock::get_attributes()
	 */
	protected function get_attributes()
	{
		$attributes = parent::get_attributes();

		$attributes[Element::GROUPS]['visibility'] = array
		(
			'title' => 'Visibility',
			'weight' => 400
		);

		return $attributes;
	}

	/**
	 * Adds the `title`, `is_online`, `uid` and `siteid` elements.
	 *
	 * The `uid` and `siteid` elements are added according to the context.
	 *
	 * @see Icybee.EditBlock::get_children()
	 */
	protected function get_children()
	{
		$values = $this->values;

		return array_merge
		(
			parent::get_children(), array
			(
				Node::TITLE => new TitleSlugCombo
				(
					array
					(
						Form::LABEL => 'title',
						Element::REQUIRED => true,
						TitleSlugCombo::T_NODEID => $values[Node::NID],
						TitleSlugCombo::T_SLUG_NAME => 'slug'
					)
				),

				Node::UID => $this->get_control__user(),
				Node::SITEID => $this->get_control__site(),	// TODO-20100906: this should be added by the "sites" modules using the alter event.
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
	protected function get_control__user()
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
	protected function get_control__site()
	{
		global $core;

		if (!$core->user->has_permission(Module::PERMISSION_MODIFY_BELONGING_SITE, $this->module))
		{
			return;
		}

		$sites = $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs;

		if (count($sites) < 2)
		{
			$this->attributes[Form::HIDDENS][Node::SITEID] = $core->site_id;

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