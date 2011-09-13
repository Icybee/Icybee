<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks\Nodes;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use BrickRouge\Element;
use BrickRouge\Form;

class Attachments
{
	/**
	 * Returns the attachments of the given node.
	 *
	 * @param Node $ar
	 *
	 * @return array|null An array of attachments or null if there is none.
	 */
	public static function get_attachments(Node $ar)
	{
		global $core;

		$nodes = $core->models['nodes.attachments']
		->find_by_nodeid($ar->nid)
		->joins('INNER JOIN {prefix}nodes ON(nid = fileid)')
		->select('fileid, attached.title, constructor')
		->where('is_online = 1')
		->order('weight')->all(PDO::FETCH_OBJ);

		if (!$nodes)
		{
			return;
		}

		$nodes_by_id = array();
		$ids_by_constructor = array();

		foreach ($nodes as $node)
		{
			$nid = $node->fileid;
			$nodes_by_id[$nid] = $node;
			$ids_by_constructor[$node->constructor][] = $nid;
		}

		foreach ($ids_by_constructor as $constructor => $ids)
		{
			$records = $core->models[$constructor]->find($ids);

			foreach ($records as $record)
			{
				$nid = $record->nid;
				$node = $nodes_by_id[$nid];
				$nodes_by_id[$nid] = $record;

				$record->label = $node->title ? $node->title : $record->title;
			}
		}

		return array_values($nodes_by_id);
	}

	/**
	 * Alters the "edit" block to adds the "attachments" group with a WdAttachmentsElement used to
	 * manage node attachments.
	 *
	 * @param Event $event
	 */
	public static function on_alter_block_edit(Event $event, Module\Nodes $sender)
	{
		global $core;

		if ($sender instanceof Module\Files)
		{
			return;
		}

		$scope = $core->registry['nodes_attachments.scope'];

		if (!$scope)
		{
			return;
		}

		$scope = explode(',', $scope);

		if (!in_array($sender->flat_id, $scope))
		{
			return;
		}

		$event->tags = wd_array_merge_recursive
		(
			$event->tags, array
			(
				Element::T_GROUPS => array
				(
					'attachments' => array
					(
						'title' => '.attachments',
						'class' => 'form-section flat'
					)
				),

				Element::T_CHILDREN => array
				(
					new \WdAttachmentsElement
					(
						array
						(
							Element::T_GROUP => 'attachments',

							\WdAttachmentsElement::T_NODEID => $event->key,
							\WdAttachmentsElement::T_HARD_BOND => true
						)
					)
				)
			)
		);
	}

	public static function on_alter_block_config(Event $event, Module\Files $sender)
	{
		global $core;

		if (get_class($sender) != 'ICanBoogie\Module\Files')
		{
			return;
		}

		$scope = array();

		foreach ($core->modules->descriptors as $constructor => $descriptor)
		{
			if (!isset($core->modules[$constructor]) || $constructor == 'nodes')
			{
				continue;
			}

			if (!Module::is_extending($constructor, 'nodes'))
			{
				continue;
			}

			$constructor = strtr($constructor, '.', '_');
			$scope[$constructor] = t($descriptor[Module::T_TITLE]);
		}

		asort($scope);

		$scope_value = $core->registry["nodes_attachments.scope"];

		if ($scope_value)
		{
			$scope_value = explode(',', $scope_value);
			$scope_value = array_combine($scope_value, array_fill(0, count($scope_value), true));
		}

		$event->tags = wd_array_merge_recursive
		(
			$event->tags, array
			(
				Element::T_GROUPS => array
				(
					'attachments' => array
					(
						'title' => '.attachments',
						'class' => 'form-section flat'
					)
				),

				Element::T_CHILDREN => array
				(
					'global[nodes_attachments.scope]' => new Element
					(
						Element::E_CHECKBOX_GROUP, array
						(
							Form::T_LABEL => t('nodes_attachments.element.label.scope'),
							Element::T_OPTIONS => $scope,
							Element::T_GROUP => 'attachments',

							'class' => 'list combo',
							'value' => $scope_value
						)
					)
				)
			)
		);
	}

	/**
	 * Clears the current registry values for the 'nodes_attachments.scope' key, before the
	 * new one are saved. This is beacause unchecked values don't return 'off', they are just not
	 * defined.
	 *
	 * @param Event $event
	 */

	private static $config_scope;

	public static function before_operation_config(Event $event, Operation\Files\Config $sender)
	{
		$params = &$sender->params;

		if (isset($params['global']['nodes_attachments.scope']))
		{
			self::$config_scope = $params['global']['nodes_attachments.scope'];
		}

		unset($params['global']['nodes_attachments.scope']);
	}

	public static function on_operation_config(Event $event, Operation\Files\Config $sender)
	{
		global $core;

		$scope = null;

		if (self::$config_scope)
		{
			$scope = array_keys(self::$config_scope);
			$scope = implode(',', $scope);
		}

		$core->registry['nodes_attachments.scope'] = $scope;
	}

	/**
	 * The `wdp:node:attachments` markup can be used to render a node attachments.
	 *
	 * There is actually two modes for rendering the attachments, depending on their number:
	 *
	 * - No attachment: nothing is rendered.
	 *
	 * - One attachment:
	 *
	 *     <div class="node-attachments">
	 *     <p><a href="#{@url('download')}">#{t:Download attachment}</a>
	 *     <span class="metas">(#{@extension} – #{@size.format_size()}</span></p>
	 *     </div>
	 *
	 * - More than on attachment:
	 *
	 *     <div class="node-attachments">
	 *     <h5>#{t:Attached files}</h5>
	 *     <ul>
	 *     <wdp:foreach>
	 *     <p><a href="#{@url('download')}">#{@label}</a>
	 *     <span class="metas">(#{@extension} – #{@size.format_size()}</span></p>
	 *     </wdp:foreach>
	 *     </ul>
	 *     </div>
	 *
	 * Use the I18n scope to translate "Download attachment" or "Attachments" to module specific
	 * translations such as "Download press release" or "Press release attachments".
	 *
 	 * Attachments are created using the "resources.files.attached" module.
	 *
	 * @param array $args
	 * @param WdPatron $patron
	 * @param string|null $template
	 * @return string|null The rendered attached file(s), or null if no files were attached.
	 */
	static public function markup_node_attachments(array $args=array(), WdPatron $patron, $template)
	{
		$target = $patron->context['this'];
		$files = $target->attachments;

		if (!$files)
		{
			return;
		}

		$rc = '<div class="node-attachments">';

		if (count($files) == 1)
		{
			$file = $files[0];

			$rc .= '<p>' . self::make_link($file, t('Download attachment')) . '</p>';
		}
		else
		{
			$rc .= '<h5>' . t('Attachments') . '</h5>';
			$rc .= '<ul>';

			foreach ($files as $file)
			{
				$rc .= '<li>' . self::make_link($file) . '</li>';
			}

			$rc .= '</ul>';
		}

		return $rc . '</div>';
	}

	static private function make_link(ActiveRecord\File $file, $label=null)
	{
		if (!$label)
		{
			$label = $file->label;
		}

		return '<a href="' . wd_entities($file->url('download')) . '">' . wd_entities($label) . '</a> <span class="metas">(' . $file->extension . ' &ndash; ' . wd_format_size($file->size) . ')</span>';
	}
}