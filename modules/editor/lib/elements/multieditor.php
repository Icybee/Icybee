<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use Brickrouge\Document;
use Brickrouge\Element;

/**
 * An element that can change its editor.
 *
 * @property Element $editor The editor element.
 */
class MultiEditorElement extends Element
{
	const EDITOR_TAGS = '#meditor-tags';
	const SELECTOR_NAME = '#meditor-selector-name';
	const NOT_SWAPPABLE = '#meditor-not-wappable';

	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('assets/elements.css');
		$document->js->add('assets/elements.js');
	}

	protected $editor_id;

	public function __construct($editor, array $attributes)
	{
		$this->editor_id = $editor ? $editor : 'rte';

		parent::__construct
		(
			'div', $attributes + array
			(
				self::SELECTOR_NAME => 'editor',

				'class' => 'editor-wrapper'
			)
		);
	}

	protected function get_editor()
	{
		global $core;

		$editor_id = $this->editor_id;
		$editor = $core->editors[$editor_id];
		$element = $editor->from
		(
			($this[self::EDITOR_TAGS] ?: array()) + array
			(
				Element::REQUIRED => $this[self::REQUIRED],
				Element::DEFAULT_VALUE => $this[self::DEFAULT_VALUE],

				'name' => $this['name'],
// 				'value' => $editor->unserialize($this['value'])
				'value' => $this['value']
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

		return $element;
	}

	/**
	 * Adds the `contents-name` and `selector-name` properties.
	 *
	 * @see Brickrouge.Element::alter_dataset()
	 */
	protected function alter_dataset(array $dataset)
	{
		$dataset = parent::alter_dataset($dataset);

		$dataset['contents-name'] = $this['name'];
		$dataset['selector-name'] = $this[self::SELECTOR_NAME];

		return $dataset;
	}

	/**
	 * The inner HTML of the element includes the editor element and the selector element.
	 *
	 * If the editor is not swappable an hidden element is used instead of the selector element.
	 *
	 * @see Brickrouge.Element::render_inner_html()
	 */
	protected function render_inner_html()
	{
		$html = (string) $this->editor;
		$editor_id = $this->editor_id;

		if ($this[self::NOT_SWAPPABLE])
		{
			$html .= new Element
			(
				'hidden', array
				(
					'name' => $this[self::SELECTOR_NAME],
					'value' => $editor_id
				)
			);
		}
		else
		{
			$options = (string) new SelectorElement
			(
				array
				(
					Element::LABEL => 'Editor',
					Element::LABEL_POSITION => 'before',

					'name' => $this[self::SELECTOR_NAME],
					'class' => 'editor-selector',
					'value' => $editor_id
				)
			);

			if ($options)
			{
				$html .= '<div class="editor-options clearfix"><div style="float: right">' . $options . '</div></div>';
			}
		}

		return $html;
	}
}