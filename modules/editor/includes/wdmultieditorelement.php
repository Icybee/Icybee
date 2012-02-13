<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brickrouge\Element;
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
					Element::REQUIRED => $this->get(self::REQUIRED),
					Element::DEFAULT_VALUE => $this->get(self::DEFAULT_VALUE),

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
				Element::LABEL => '.editor',
				Element::LABEL_POSITION => 'before',
				Element::OPTIONS => $options,

				'name' => $this[self::T_SELECTOR_NAME],
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
			$rc .= '<input type="hidden" name="' . $this[self::T_SELECTOR_NAME] .'" value="' . $this->editor_name . '" />';
		}
		else
		{
			$options = $this->options();

			if ($options)
			{
				$rc .= '<div class="editor-options clearfix">' . $options . '</div>';
			}
		}

		$this->dataset['contents-name'] = $this['name'];
		$this->dataset['selector-name'] = $this[self::T_SELECTOR_NAME];

		return $rc;
	}
}