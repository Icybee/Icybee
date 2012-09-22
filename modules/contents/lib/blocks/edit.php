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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Editor\MultiEditorElement;

class EditBlock extends \ICanBoogie\Modules\Nodes\EditBlock
{
	protected function get_attributes()
	{
		$attributes = parent::get_attributes();

		$attributes[Element::GROUPS] = array_merge
		(
			$attributes[Element::GROUPS], array
			(
				'contents' => array
				(
					'title' => 'Content'
				),

				'date' => array
				(

				)
			)
		);

		return $attributes;
	}

	protected function get_children()
	{
		global $core;

		$module_flat_id = $this->module->flat_id;

		$default_editor = $core->site->metas->get($module_flat_id . '.default_editor', 'rte');
		$use_multi_editor = $core->site->metas->get($module_flat_id . '.use_multi_editor');

		if ($use_multi_editor)
		{

		}
		else
		{

		}

		$values = $this->values;

		return array_merge
		(
			parent::get_children(), array
			(
				Content::SUBTITLE => new Text
				(
					array
					(
						Form::LABEL => 'subtitle'
					)
				),

				Content::BODY => new MultiEditorElement
				(
					$values['editor'] ? $values['editor'] : $default_editor, array
					(
						Element::LABEL_MISSING => 'Contents',
						Element::GROUP => 'contents',
						Element::REQUIRED => true,

						'rows' => 16
					)
				),

				Content::EXCERPT => $core->editors['rte']->from
				(
					array
					(
						Form::LABEL => 'excerpt',
						Element::GROUP => 'contents',
						Element::DESCRIPTION => "excerpt",

						'rows' => 3
					)
				),

				Content::DATE => new \Brickrouge\Date
				(
					array
					(
						Form::LABEL => 'Date',
						Element::REQUIRED => true,
						Element::DEFAULT_VALUE => date('Y-m-d')
					)
				),

				Content::IS_HOME_EXCLUDED => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => "is_home_excluded",
						Element::GROUP => 'visibility',
						Element::DESCRIPTION => "is_home_excluded"
					)
				)
			)
		);
	}

	protected function get_values()
	{
		global $core;

		$values = parent::get_values();

		if (isset($values['editor']) && isset($values['body']))
		{
			$editor = $core->editors[$values['editor']];

			$values['body'] = $editor->unserialize($values['body']);
		}

		return $values;
	}
}