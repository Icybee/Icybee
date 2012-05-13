<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brickrouge\Widget;

class adjustnode_WdEditorElement extends WdEditorElement
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	static public function to_content($value, $content_id, $page_id)
	{
		if (!$value)
		{
			return;
		}

		return $value;
	}

	static public function render($content)
	{
		global $core;

		if (!is_numeric($content))
		{
			$content = json_decode($content);
		}

		if (!$content)
		{
			return;
		}

		return $core->models['nodes'][$content];
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

		/*DIRTY
		$config = (array) $this[self::T_CONFIG] ?: array();

		// TODO-20100816: rename 'scope' as 'contructor' ?

		if (isset($config['constructor']))
		{
			$scope = $config['constructor'];
		}
		else
		{
			$scope = (isset($config['scope'])) ? $config['scope'] : 'nodes';
		}
		*/

		$class = 'ICanBoogie\Modules\Nodes\PopNode';
		$constructor = $this['data-constructor'] ?: 'nodes';

		if ($constructor == 'images')
		{
			$class = 'Brickrouge\Widget\PopImage';
		}

		$rc .= (string) new $class
		(
			array
			(
				\ICanBoogie\Modules\Nodes\PopNode::T_CONSTRUCTOR => $constructor,

				'name' => $name,
				'value' => $value
			)
		);

		return $rc;
	}
}