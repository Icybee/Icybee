<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\I18n;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\Event;
use ICanBoogie\Modules;

use Brickrouge\Element;
use Brickrouge\Form;

class Hooks
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
	public static function on_nodes_editblock_alter_children(Event $event, \ICanBoogie\Modules\Nodes\EditBlock $block)
	{
		global $core;

		$site = $core->site;

		if (!$site->nativeid || !isset($core->modules['i18n']))
		{
			return;
		}

		$module = $event->module;
		$languages = $module->model->where('language != ""')->count('language');

		if (!count($languages)/* || current($languages) == $core->site->language*/)
		{
			return;
		}

		$children = &$event->children;

		if (array_key_exists(Node::LANGUAGE, $children) && !$children[Node::LANGUAGE])
		{
			return;
		}

		$event->attributes[Element::GROUPS]['i18n'] = array
		(
			'title' => 'i18n',
			'weight' => 100
		);

		if (!array_key_exists(Node::LANGUAGE, $event->attributes[Form::HIDDENS]))
		{
			$children[Node::LANGUAGE] = new NodeLanguageElement
			(
				array
				(
					Element::GROUP => 'i18n',
					'data-native-language' => $site->native->language,
					'data-site-language' => $site->language
				)
			);
		}

		$children[Node::NATIVEID] = new NodeNativeElement
		(
			array
			(
				Element::GROUP => 'i18n',
				NodeNativeElement::CONSTRUCTOR => $module->id
			)
		);
	}
}