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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to configure contents.
 */
class ConfigBlock extends \Icybee\ConfigBlock
{
	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'limits' => array
					(
						'title' => 'limits'
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		$ns = $this->module->flat_id;

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				"local[$ns.default_editor]" => new Text
				(
					array
					(
						Form::LABEL => 'default_editor'
					)
				),

				"local[$ns.use_multi_editor]" => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'use_multi_editor'
					)
				),

				"local[$ns.limits.home]" => new Text
				(
					array
					(
						Form::LABEL => 'limits_home',
						Element::DEFAULT_VALUE => 3,
						Element::GROUP => 'limits'
					)
				),

				"local[$ns.limits.list]" => new Text
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
}