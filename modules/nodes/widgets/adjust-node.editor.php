<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Widget;

class adjustnode_WdEditorElement extends WdEditorElement
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct('div', $tags);
	}

	static public function to_content(array $params, $content_id, $page_id)
	{
		if (empty($params['contents']))
		{
			return;
		}

		return json_encode($params['contents']);
	}

	static public function render($contents)
	{
		global $core;

		$value = json_decode($contents);

		if ($value === null)
		{
			return;
		}

		return $core->models['nodes'][$value];
	}

	protected function render_inner_html()
	{
		$rc = parent::render_inner_html();

		$value = $this->get('value');
		$name = $this->get('name');

		$value = json_decode($value);
		$config = (array) $this->get(self::T_CONFIG, array());

		// TODO-20100816: rename 'scope' as 'contructor' ?

		if (isset($config['constructor']))
		{
			$scope = $config['constructor'];
		}
		else
		{
			$scope = (isset($config['scope'])) ? $config['scope'] : 'nodes';
		}

		$class = 'BrickRouge\Widget\PopNode';

		if ($scope == 'images')
		{
			$class = 'BrickRouge\Widget\PopImage';
		}

		$rc .= (string) new $class
		(
			array
			(
				Widget\PopNode::T_CONSTRUCTOR => $scope,

				'name' => $name,
				'value' => $value
			)
		);

		return $rc;
	}
}