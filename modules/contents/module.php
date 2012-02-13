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
use ICanBoogie\ActiveRecord\Query;

use Brickrouge;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use WdPatron;
use WdMultiEditorElement;
use moo_WdEditorElement;
use WdDateElement;

/**
 * The "contents" module extends the "system.nodes" module by offrering a subtitle, a body
 * (with a customizable editor), an optional excerpt, a date and a new visibility option (home).
 */
class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_HOME_INCLUDE = 'home_include';
	const OPERATION_HOME_EXCLUDE = 'home_exclude';

	/**
	 * Overrites the "view", "list" and "home" views to provide different titles and providers.
	 *
	 * @see Icybee.Module::__get_views()
	 */
	protected function __get_views()
	{
		return wd_array_merge_recursive
		(
			parent::__get_views(), array
			(
				'view' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				),

				'list' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				),

				'home' => array
				(
					'provider' => 'Icybee\Views\Contents\Provider'
				)
			)
		);
	}

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', /*'category',*/ 'uid', 'is_home_excluded', 'is_online', 'date', 'modified'
				)
			)
		);
	}

	protected function block_config()
	{
		return array
		(
			Element::GROUPS => array
			(
				'limits' => array
				(
					'title' => 'limits'
				)
			),

			Element::CHILDREN => array
			(
				"local[$this->flat_id.default_editor]" => new Text
				(
					array
					(
						Form::LABEL => 'default_editor'
					)
				),

				"local[$this->flat_id.use_multi_editor]" => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'use_multi_editor'
					)
				),

				"local[$this->flat_id.limits.home]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_home',
						Element::DEFAULT_VALUE => 3,
						Element::GROUP => 'limits'
					)
				),

				"local[$this->flat_id.limits.list]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_list',
						Element::DEFAULT_VALUE => 10,
						Element::GROUP => 'limits'
					)
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$default_editor = $core->site->metas->get($this->flat_id . '.default_editor', 'moo');
		$use_multi_editor = $core->site->metas->get($this->flat_id . '.use_multi_editor');

		if ($use_multi_editor)
		{

		}
		else
		{

		}

		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission),

			array
			(
				Element::GROUPS => array
				(
					'contents' => array
					(
						'title' => 'Contents'
					),

					'date' => array
					(

					)
				),

				Element::CHILDREN => array
				(
					Content::SUBTITLE => new Text
					(
						array
						(
							Form::LABEL => 'subtitle'
						)
					),

					Content::BODY => new WdMultiEditorElement
					(
						$properties['editor'] ? $properties['editor'] : $default_editor, array
						(
							Element::LABEL_MISSING => 'Contents',
							Element::GROUP => 'contents',
							Element::REQUIRED => true,

							'rows' => 16
						)
					),

					Content::EXCERPT => new moo_WdEditorElement
					(
						array
						(
							Form::LABEL => 'excerpt',
							Element::GROUP => 'contents',
							Element::DESCRIPTION => "excerpt",

							'rows' => 3
						)
					),

					Content::DATE => new Brickrouge\Date
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
			)
		);
	}
}