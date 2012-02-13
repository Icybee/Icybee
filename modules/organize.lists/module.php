<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Organize\Lists;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Modules;

use Brickrouge\Element;
use Brickrouge\Form;

class Module extends Modules\Nodes\Module
{
	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', 'uid', 'is_online', 'modified'
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$value = array();

		if (isset($properties['nodes']))
		{
			$value = array_map('intval', $properties['nodes']);
		}
		else if ($properties[Node::NID])
		{
			$value = $this->model('nodes')->select('nodeid')->where('listid = ?', $properties[Node::NID])->order('weight')->all(PDO::FETCH_COLUMN);
		}

		$scope = $properties['scope'] ? $properties['scope'] : 'nodes';
		$scopes = $this->getScopes();

		$rc = wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::CHILDREN => array
				(
					'scope' => new Element
					(
						'select', array
						(
							Form::LABEL => 'Portée',
							Element::OPTIONS => array('nodes' => '') + $scopes,
							Element::DESCRIPTION => "La « portée » permet de choisir le type
							des entrées qui composent la liste.",

							'value' => $scope
						)
					),

					'nodes' => new \WdAdjustNodesListWidget
					(
						array
						(
							//Form::LABEL => 'Entrées',
							\WdAdjustNodesListWidget::T_SCOPE => $scope,
							\WdAdjustNodesListWidget::T_LIST_ID => $properties[Node::NID],

							'value' => $value
						)
					),

					'description' => new \moo_WdEditorElement
					(
						array
						(
							Form::LABEL => 'Description',

							'rows' => 5
						)
					)
				)
			)
		);

		$core->document->js->add('public/edit.js');

		return $rc;
	}

	protected function getScopes()
	{
		global $core;

		$scopes = array();

		foreach ($core->modules->descriptors as $module_id => $descriptor)
		{
			if (empty($descriptor[self::T_MODELS]['primary']))
			{
				continue;
			}

			if (!isset($core->modules[$module_id]))
			{
				continue;
			}

			$model = $descriptor[self::T_MODELS]['primary'];

			$is_instance = Model::is_extending($model, 'nodes');

			if (!$is_instance)
			{
				continue;
			}

			$scopes[$module_id] = t($descriptor[self::T_TITLE]);
		}

		asort($scopes);

		return $scopes;
	}
}