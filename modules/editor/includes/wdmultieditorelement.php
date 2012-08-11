<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Event;

use Brickrouge\Document;
use Brickrouge\Element;

class WdMultiEditorElement extends Element
{
	const T_EDITOR_TAGS = '#meditor-tags';
	const T_SELECTOR_NAME = '#meditor-selector-name';
	const T_NOT_SWAPPABLE = '#meditor-not-wappable';

	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../public/multi.css');
		$document->js->add('../public/multi.js');
	}

	protected $editor;
	protected $editor_name;

	public function __construct($editor, array $attributes)
	{
		$this->editor_name = $editor ? $editor : 'moo';

		parent::__construct
		(
			'div', $attributes + array
			(
				self::T_SELECTOR_NAME => 'editor',

				'class' => 'editor-wrapper'
			)
		);
	}

	public function editor()
	{
		global $core;

		if ($this->editor)
		{
			return $this->editor;
		}

		$editor_name = $this->editor_name;
		$editor = $core->editors[$editor_name];
		$element = $editor->create_element
		(
			($this[self::T_EDITOR_TAGS] ?: array()) + array
			(
				Element::REQUIRED => $this[self::REQUIRED],
				Element::DEFAULT_VALUE => $this[self::DEFAULT_VALUE],

				'name' => $this['name'],
				'value' => $editor->unserialize($this['value'])
			)
		);

		if ($element->type == 'textarea')
		{
			$rows = $this['rows'];

			if ($rows !== null)
			{
				$element['rows'] = $rows;
			}
		}

		return $this->editor = (string) $element;
	}

	protected function options()
	{
		// TODO-20120811: use collection!

		$options = array
		(
			'raw' => 'Texte brut',
			'moo' => 'HTML WYSIWYG',
			'rte' => 'HTML WYSIWYG',
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
				Element::LABEL => 'Editor',
				Element::LABEL_POSITION => 'before',
				Element::OPTIONS => $options,

				'name' => $this[self::T_SELECTOR_NAME],
				'class' => 'editor-selector',
				'value' => $this->editor_name
			)
		);

		return $el;
	}

	protected function alter_dataset(array $dataset)
	{
		$dataset = parent::alter_dataset($dataset);

		$dataset['contents-name'] = $this['name'];
		$dataset['selector-name'] = $this[self::T_SELECTOR_NAME];

		return $dataset;
	}

	protected function render_inner_html()
	{
		$html = $this->editor();

		if ($this[self::T_NOT_SWAPPABLE])
		{
			$html .= new Element
			(
				'hidden', array
				(
					'name' => $this[self::T_SELECTOR_NAME],
					'value' => $this->editor_name
				)
			);
		}
		else
		{
			$options = $this->options();

			if ($options)
			{
				$html .= '<div class="editor-options clearfix"><div style="float: right">' . $options . '</div></div>';
			}
		}

		return $html;
	}
}