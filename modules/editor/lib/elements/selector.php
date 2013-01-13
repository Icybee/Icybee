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

use ICanBoogie\I18n;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

/**
 * A selector for the available editors.
 */
class SelectorElement extends Element
{
	public function __construct(array $attributes=array())
	{
		global $core;

		$options = array();

		foreach ($core->editors as $id => $editor)
		{
			$options[$id] = I18n\t($id, array(), array('scope' => 'editor_title'));
		}

		parent::__construct
		(
			'select', $attributes + array
			(
				Element::OPTIONS => $options
			)
		);
	}

	/**
	 * @throws ElementIsEmpty if the element has no options.
	 *
	 * @see Brickrouge.Element::render_outer_html()
	 */
	protected function render_outer_html()
	{
		if (!$this[Element::OPTIONS])
		{
			throw new ElementIsEmpty;
		}

		return parent::render_outer_html();
	}
}
