<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\Event;
use ICanBoogie\Module;

use BrickRouge\Element;
use BrickRouge\Form;

class I18n
{
	/**
	 * Alters system.nodes module and submodules edit block with I18n options, allowing the user
	 * to select a language for the node and a native source target.
	 *
	 * Only the native target selector is added if the `language` property is defined in the
	 * HIDDENS array, indicating that the language is already set and cannot be modified by the
	 * user.
	 *
	 * The I18n options are not added if the following conditions are met:
	 *
	 * - The working site has no native target
	 * - The "i18n" module is disabled
	 * - Only one language is used by all the sites available.
	 * - The `language` property is defined in the CHILDREN array but is empty, indicating that
	 * the language is irrelevant for the node.
	 *
	 * @param Event $event
	 */
	public static function on_alter_block_edit(Event $event, Module\Nodes $sender)
	{
		global $core;

		if (!$core->site->nativeid || !isset($core->modules['i18n']))
		{
			return;
		}

		$languages = $sender->model->where('language != ""')->count('language');

		if (count($languages) < 2)
		{
			return;
		}

		$tags = &$event->tags;
		$children = &$tags[Element::CHILDREN];

		if (array_key_exists(Node::LANGUAGE, $children) && empty($children[Node::LANGUAGE]))
		{
			return;
		}

		$tags[Element::GROUPS]['i18n'] = array
		(
			'title' => '.i18n',
			'weight' => 100,
			'class' => 'form-section flat'
		);

		$constructor = (string) $sender;

		if (array_key_exists(Node::LANGUAGE, $event->tags[Form::HIDDENS]))
		{
			$children[Node::NATIVEID] = new \WdI18nLinkElement
			(
				array
				(
					\WdI18nElement::T_CONSTRUCTOR => $constructor
				)
			);
		}
		else
		{
			$children['i18n'] = new \WdI18nElement
			(
				array
				(
					Element::GROUP => 'i18n',
					\WdI18nElement::T_CONSTRUCTOR => $constructor
				)
			);
		}
	}
}