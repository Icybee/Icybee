<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;
use ICanBoogie\Event;

class WdMultiEditorElement extends Element
{
	const T_EDITOR_TAGS = '#meditor-tags';
	const T_SELECTOR_NAME = '#meditor-selector-name';
	const T_NOT_SWAPPABLE = '#meditor-not-wappable';

	protected $editor;
	protected $editor_name;

	public function __construct($editor, $tags)
	{
		global $core;

		$this->editor_name = $editor ? $editor : 'moo';

		parent::__construct
		(
			'div', $tags + array
			(
				self::T_SELECTOR_NAME => 'editor',

				'class' => 'editor-wrapper'
			)
		);

		$document = $core->document;

		$document->css->add('../public/multi.css');
		$document->js->add('../public/multi.js');
	}

	public function editor()
	{
		if (!$this->editor)
		{
			$editor_class = $this->editor_name . '_WdEditorElement';

			$this->editor = new $editor_class
			(
				$this->get(self::T_EDITOR_TAGS, array()) + array
				(
					Element::T_REQUIRED => $this->get(self::T_REQUIRED),
					Element::T_DEFAULT => $this->get(self::T_DEFAULT),

					'name' => $this->get('name'),
					'value' => $this->get('value')
				)
			);

			if ($this->editor->type == 'textarea')
			{
				$rows = $this->get('rows');

				if ($rows !== null)
				{
					$this->editor->set('rows', $rows);
				}
			}
		}

		return $this->editor;
	}

	protected function options()
	{
		$options = array
		(
			'raw' => 'Texte brut',
			'moo' => 'HTML WYSIWYG',
			'textmark' => 'Textmark',
			'patron' => 'Patron',
// 			'php' => 'PHP',
			'view' => 'Vue',
			'widgets' => 'Gadgets'
		);

		Event::fire
		(
			'alter.editors.options', array
			(
				'rc' => &$options
			)
		);

		$el = new Element
		(
			'select', array
			(
				Element::T_LABEL => '.editor',
				Element::T_LABEL_POSITION => 'before',
				Element::T_OPTIONS => $options,

				'name' => $this->get(self::T_SELECTOR_NAME),
				'class' => 'editor-selector',
				'value' => $this->editor_name
			)
		);

		return '<div style="float: right">' . $el . '</div>';
	}

	protected function render_inner_html()
	{
		$rc = $this->editor();

		if ($this->get(self::T_NOT_SWAPPABLE))
		{
			$rc .= '<input type="hidden" name="' . $this->get(self::T_SELECTOR_NAME) .'" value="' . $this->editor_name . '" />';
		}
		else
		{
			$options = $this->options();

			if ($options)
			{
				$rc .= '<div class="editor-options">';
				$rc .= $options;
				$rc .= '<div class="clear"></div>';
				$rc .= '</div>';
			}
		}

		$this->dataset['contents-name'] = $this->get('name');
		$this->dataset['selector-name'] = $this->get(self::T_SELECTOR_NAME);

		return $rc;
	}
}