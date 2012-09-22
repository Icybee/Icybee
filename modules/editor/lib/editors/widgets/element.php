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

use Brickrouge\Alert;

use Brickrouge\Document;
use Brickrouge\Element;

class WidgetsEditorElement extends Element implements EditorElement
{
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
			'ul', $attributes + array
			(
				'class' => 'widgets-selector combo'
			)
		);

		if ($this[Element::DESCRIPTION] === null)
		{
			$this[Element::DESCRIPTION] = "Sélectionner les widgets à afficher. Vous pouvez les ordonner par glissé-déposé.";
		}
	}

	public function render_inner_html()
	{
		global $core;

		$config = $core->configs->synthesize('widgets', 'merge');

		if (!$config)
		{
			return new Alert('There is no widget defined.', array(Alert::CONTEXT => Alert::CONTEXT_INFO));
		}

		$rc = parent::render_inner_html();

		$value = $this['value'];
		$name = $this['name'];

		$value = is_array($value) ? array_flip($value) : array();

		// TODO-20100204: check deprecated widgets ids

		$list = array_merge($value, $config);

		//\ICanBoogie\log('value: \1, list: \2 \3', array($value, $list, array_merge($value, $list)));

		foreach ($list as $id => $widget)
		{
			$rc .= '<li>';

			$rc .= new Element
			(
				Element::TYPE_CHECKBOX, array
				(
					Element::LABEL => $widget['title'],

					'name' => $name . '[' . $id . ']',
					'checked' => isset($value[$id])
				)
			);

			$rc .= '</li>';
		}

		return $rc;
	}
}