<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Widget;

use ICanBoogie\Operation;

class AdjustNodesList extends \BrickRouge\Widget
{
	const T_SCOPE = '#adjust-scope';
	const T_SEARCH_DESCRIPTION = '#adjust-search-description';
	const T_LIST_DESCRIPTION = '#adjust-list-description';
	const T_LIST_ID = '#adjust-list-id';

	public function __construct($tags=array(), $dummy=null)
	{
		global $core;

		parent::__construct
		(
			'div', $tags + array
			(
				self::T_SEARCH_DESCRIPTION => "Ci-dessus, les entrées qui peuvent être
				utilisées pour composer votre liste. Cliquez sur une entrée pour l'ajouter.
				Utilisez le champ de recherche pour filtrer les entrées.",

				self::T_LIST_DESCRIPTION => "Ci-dessus, les entrées qui composent la liste.
				L'ordre peut-être modifié par glissé-déposé.",

				'class' => 'wd-adjustnodeslist'
			)
		);

		$core->document->js->add('adjust-nodes-list.js');
		$core->document->css->add('adjust-nodes-list.css');
	}

	protected function render_inner_html()
	{
		global $core;

		$constructor = $this->get(self::T_SCOPE, 'nodes');
		$module = $core->modules[$constructor];

		$search_description = $this->get(self::T_SEARCH_DESCRIPTION);
		$list_description = $this->get(self::T_LIST_DESCRIPTION);

		$this->dataset = array_merge
		(
			$this->dataset, array
			(
				'constructor' => $constructor,
				'name' => $this->get('name'),
				'list-id' => $this->get(self::T_LIST_ID)
			)
		);

		$rc = '<div class="search">' .
		'<h4>Ajouter des entrées<span class="separator">&nbsp;:</span></h4>' .

		'<div>' .
		'<input type="text" class="search" />' .
		'</div>' .

		$this->getResults($module) .

		($search_description ? '<div class="element-description">' . $search_description . '</div>' : '') .

		'</div>';

		$rc .= '<div class="list">' .
		'<h4>Entrées qui composent la liste<span class="separator">&nbsp;:</span></h4>' .
		'<ul>' . $this->getEntries($module) . '</ul>' .

		($list_description ? '<div class="element-description">' . $list_description . '</div>' : '') .

		'</div>';

		return $rc;
	}

	protected function getResults($module)
	{
		return $module->getBlock('adjustResults');
	}

	protected function getEntries($module)
	{
		$nodes = array();
		$keys = $this->get('value');

		if ($keys)
		{
			$nodes = $module->model->find($keys);
		}

		#
		# labels
		#

		$list_id = $this->get(self::T_LIST_ID);

		if ($list_id)
		{
			global $core;

			$nodes_by_nid = array();

			foreach ($nodes as $node)
			{
				$nodes_by_nid[$node->nid] = $node;
			}

			$entries = $core->models['organize.lists/nodes']->where('listid = ?', $list_id)->all;

			foreach ($entries as $entry)
			{
				if (empty($nodes_by_nid[$entry->nodeid]))
				{
					wd_log_error
					(
						'Missing node %nodeid (%label)', array
						(
							'%nodeid' => $entry->nodeid,
							'%label' => $entry->label
						)
					);

					continue;
				}

				$nodes_by_nid[$entry->nodeid]->label = $entry->label;
			}
		}

		#
		#
		#

		$rc = '<li class="holder">Déposez ici les objets de la liste</li>';

		foreach ($nodes as $node)
		{
			$rc .= '<li class="sortable">' . self::create_entry($node, null, $module) . '</li>';
		}

		return $rc;
	}

	static public function create_entry($node, $entry, $module)
	{
		$title = $node->title;
		$label = isset($node->label) ? $node->label : $node->title;

		$rc  = '<span class="handle">↕</span>' . PHP_EOL;
		$rc .= $module->adjust_createEntry($node);
		$rc .= '<input type="text" name="labels[]" value="' . wd_entities($label) . '" title="Titre original&nbsp;: ' . wd_entities($title) . '" />';

		return $rc;
	}

	static public function operation_add(array $params)
	{
		global $core;

		$nid = $params['nid'];

		$node = $core->models['nodes'][$nid];
		$module = $core->modules[$node->constructor];

		$operation = new widget_adjust_nodes_list__add_WdOperation($module, 'widget_ajust_nodes_list_add', array(WdOperation::KEY => $params['nid']));

		return $operation;
	}
}

class widget_adjust_nodes_list__add_WdOperation extends Operation
{
	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		return WdAdjustNodesListWidget::create_entry($this->record, null, $this->module);
	}
}