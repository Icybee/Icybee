<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents;

use ICanBoogie\ActiveRecord\Content;
use ICanBoogie\Modules\Editor\RTEEditorElement;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class EditBlock extends \ICanBoogie\Modules\Nodes\EditBlock
{
	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'contents' => array
					(
						'title' => 'Content'
					),

					'date' => array
					(

					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$module_flat_id = $this->module->flat_id;

		$default_editor = $core->site->metas->get($module_flat_id . '.default_editor', 'moo');
		$use_multi_editor = $core->site->metas->get($module_flat_id . '.use_multi_editor');

		if ($use_multi_editor)
		{

		}
		else
		{

		}

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Content::SUBTITLE => new Text
				(
					array
					(
						Form::LABEL => 'subtitle'
					)
				),

				Content::BODY => new \WdMultiEditorElement
				(
					$properties['editor'] ? $properties['editor'] : $default_editor, array
					(
						Element::LABEL_MISSING => 'Contents',
						Element::GROUP => 'contents',
						Element::REQUIRED => true,

						'rows' => 16
					)
				),

				Content::EXCERPT => new RTEEditorElement
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
}