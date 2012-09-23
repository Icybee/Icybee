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

use Brickrouge\Element;

/**
 * "Node" editor element.
 */
class NodeEditorElement extends Element implements EditorElement
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	protected function render_inner_html()
	{
		$rc = parent::render_inner_html();

		$value = $this['value'];
		$name = $this['name'];

		if ($value && !is_numeric($value))
		{
			$value = json_decode($value);
		}

		$class = 'Icybee\Modules\Nodes\PopNode';
		$constructor = $this['data-constructor'] ?: 'nodes';

		if ($constructor == 'images')
		{
			$class = 'Icybee\Modules\Images\PopImage';
		}

		$rc .= (string) new $class
		(
			array
			(
				\Icybee\Modules\Nodes\PopNode::T_CONSTRUCTOR => $constructor,

				'name' => $name,
				'value' => $value
			)
		);

		return $rc;
	}
}