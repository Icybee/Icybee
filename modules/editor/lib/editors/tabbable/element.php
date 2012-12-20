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
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

/**
 * "Tabbable" editor element.
 */
class TabbableEditorElement extends Element implements EditorElement
{
	/**
	 * Adds the `editor.css` and `editor.js` assets.
	 *
	 * @param Document $document
	 */
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('assets/editor.css');
		$document->js->add('assets/editor.js');
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'div', $attributes + array
			(
				Element::WIDGET_CONSTRUCTOR => 'TabbableEditor',

				'class' => 'editor editor--tabbable'
			)
		);
	}

	/**
	 * Adds the `control-name` property.
	 *
	 * @see Brickrouge.Element::alter_dataset()
	 */
	protected function alter_dataset(array $dataset)
	{
		return parent::alter_dataset(array_merge($dataset, array(

			'control-name' => $this['name']

		)));
	}

	protected function render_inner_html()
	{
		$value = $this['value'];
		$panes = $value ? array_values((array) $this['value']) : array
		(
			array
			(
				'title' => 'New tab',
				'editor_id' => 'rte',
				'serialized_content' => null
			)
		);

		foreach ($panes as $id => &$pane)
		{
			$pane['name'] = $this['name'] . '[' . $id . ']';
		}

		$nav = $this->render_tabbable_nav($panes);
		$content = $this->render_tabbable_content($panes);

		return <<<EOT
<div class="tabbable" tabindex="0">

	$nav
	$content

</div>
EOT;
	}

	protected function render_tabbable_nav(array $panes)
	{
		$first = key($panes);
		$html = '';

		foreach ($panes as $i => $pane)
		{
			$tab = static::create_tab($pane);

			if (!$i)
			{
				$tab->add_class('active');
			}

			$html .= $tab;
		}

		$html .= '<li><a href="#" title="Nouvel onglet" data-create="tab">+</a></li>';

		return '<ul class="nav nav-tabs">' . $html . '</ul>';
	}

	protected function render_tabbable_content(array $panes)
	{
		$html = '';

		foreach ($panes as $i => $pane)
		{
			$element = static::create_pane($pane);

			if (!$i)
			{
				$element->add_class('active');
			}

			$html .= $element;
		}

		return '<div class="tab-content combo">' . $html . '</div>';
	}

	/**
	 * Creates a tab element.
	 *
	 * The method is also used by the {@link TabbableNewPaneOperation}.
	 *
	 * @param array $pane
	 *
	 * @return \Brickrouge\Element
	 */
	static public function create_tab(array $pane)
	{
		return new Element
		(
			'li', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						'a', array
						(
							Element::INNER_HTML => '<span class="title" data-recieves="title">' . $pane['title'] . '</span><span class="close" data-removes="tab">&times;</span>',

							'href' => '#',
							'data-toggle' => 'tab',
							'tabindex' => -1
						)
					)
				)
			)
		);
	}

	/**
	 * Creates a pane element.
	 *
	 * The method is also used by the {@link TabbableNewPaneOperation}.
	 *
	 * @param array $pane
	 *
	 * @return \Brickrouge\Element
	 */
	static public function create_pane(array $pane)
	{
		global $core;

		$editor_id = $pane['editor_id'];
		$name = $pane['name'];
		$editor = $core->editors[$editor_id];
		$value = null;

		if (!empty($pane['content']))
		{
			$value = $pane['content'];
		}
		else if (!empty($pane['serialized_content']))
		{
			$value = $editor->unserialize($pane['serialized_content']);
		}

		$content = new Group
		(
			array
			(
				Element::CHILDREN => array
				(
					'title' => new Text
					(
						array
						(
							Element::REQUIRED => true,

							'name' => "{$name}[title]",
							'value' => $pane['title'],

							'data-provides' => 'title'
						)
					),

					'content' => new MultiEditorElement
					(
						$editor_id, array
						(
							MultiEditorElement::SELECTOR_NAME => "{$name}[editor_id]",

							'name' => "{$name}[content]",
							'value' => $value,

							'data-provides' => 'content'
						)
					)
				)
			)
		);

		return new Element
		(
			'div', array
			(
				Element::INNER_HTML => $content,

				'class' => 'tab-pane'
			)
		);
	}
}