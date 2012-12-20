<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes\Attachments;

use Icybee\Modules\Nodes\Node;
use ICanBoogie\Event;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;

class Hooks
{
	static public function on_node_save(Event $event, \Icybee\Modules\Nodes\SaveOperation $operation)
	{
		global $core;

		$params = &$event->request->params;
		$nid = $event->rc['key'];

		if (empty($params['nodes_attachments']))
		{
			return;
		}

		$model = $core->models['nodes.attachments'];

		$files_model = $core->models['files'];
		$images_model = $core->models['images'];

		$root = \ICanBoogie\DOCUMENT_ROOT;
		$repository = $core->config['repository.temp'] . '/';

		$weight = 0;
		$attached_fileids = array();

		foreach ($params['nodes_attachments'] as $attached_params)
		{
			if (isset($attached_params['file']))
			{
				#
				# create
				#

				$path = $repository . $attached_params['file'];

				$attached_params['path'] = $path;
				$attached_params['is_online'] = true;

				if (getimagesize($root . $path))
				{
					$fileid = \Icybee\Modules\Images\Image::from
					(
						$attached_params + array
						(
							Node::SITEID => $core->site_id,
							Node::CONSTRUCTOR => 'images'
						)
					)
					->save();
				}
				else
				{
					$fileid = \Icybee\Modules\Files\File::from
					(
						$attached_params + array
						(
							Node::SITEID => $core->site_id,
							Node::CONSTRUCTOR => 'files'
						)
					)
					->save();
				}

				if (!$fileid)
				{
					$operation->errors[] = t('Unable to save file: \1', array($attached_params));

					continue;
				}

				$model->save
				(
					array
					(
						'nodeid' => $nid,
						'fileid' => $fileid,
						'title' => $attached_params['title'],
						'weight' => $weight
					)
				);

				$attached_fileids[] = $fileid;
			}
			else if (isset($attached_params['fileid']))
			{
				$fileid = $attached_params['fileid'];

				if ($attached_params['title'] == '!delete')
				{
					$file = $files_model[$fileid];

					$delete_request = Request::from(array('path' => "/api/{$file->constructor}/{$fileid}/delete"));
					$delete_request->post();

					continue;
				}
				else if ($attached_params['title'] == '!remove')
				{
					continue;
				}

				$model->execute
				(
					'UPDATE {self} SET title = ?, weight = ? WHERE nodeid = ? AND fileid = ?', array
					(
						$attached_params['title'], $weight, $nid, $fileid
					)
				);

				$attached_fileids[] = $fileid;
			}

			$weight++;
		}

		#
		# we remove the link to unspecified files.
		#

		$model->execute
		(
			'DELETE FROM {self} WHERE nodeid = ?' . ($attached_fileids ? ' AND fileid NOT IN(' . implode(',', $attached_fileids) . ')' : ''), array
			(
				$nid
			)
		);
	}

	/**
	 * Deletes attachment when the associated node is delete.
	 *
	 * @param Event $event
	 * @param \Icybee\Modules\Nodes\DeleteOperation $operation
	 */
	static public function on_node_delete(Event $event, \Icybee\Modules\Nodes\DeleteOperation $operation)
	{
		global $core;

		//TODO-20120115: if the attachment is hard we should also delete assocated files.

		$core->models['nodes.attachments']->filter_by_nodeid($operation->key)->delete();
	}

	/**
	 * Deletes attachment when the associated file is deleted.
	 *
	 * @param Event $event
	 * @param \Icybee\Modules\Files\DeleteOperation $operation
	 */
	static public function on_file_delete(Event $event, \Icybee\Modules\Files\DeleteOperation $operation)
	{
		global $core;

		$core->models['nodes.attachments']->filter_by_fileid($operation->key)->delete();
	}

	/**
	 * Returns the attachments of the given node.
	 *
	 * @param Node $ar
	 *
	 * @return array|null An array of attachments or null if there is none.
	 */
	static public function get_attachments(Node $ar)
	{
		global $core;

		$nodes = $core->models['nodes.attachments']
		->filter_by_nodeid($ar->nid)
		->joins('INNER JOIN {prefix}nodes ON(nid = fileid)')
		->select('fileid, attachment.title, constructor')
		->where('is_online = 1')
		->order('weight')->all(\PDO::FETCH_OBJ);

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
	 * Alters the "edit" block to adds the "attachments" group with a Icybee\Modules\Nodes\Attachments\AttachmentsElement used to
	 * manage node attachments.
	 *
	 * @param Event $event
	 */
	static public function on_editblock_alter_children(Event $event, \Icybee\Modules\Nodes\EditBlock $block)
	{
		global $core;

		if ($block instanceof \Icybee\Modules\Files\EditBlock)
		{
			return;
		}

		$scope = $core->registry['nodes_attachments.scope'];

		if (!$scope)
		{
			return;
		}

		$scope = explode(',', $scope);

		if (!in_array($event->module->flat_id, $scope))
		{
			return;
		}

		$event->attributes[Element::GROUPS]['attachments'] = array
		(
			'title' => 'Attachments'
		);

		$event->children = array_merge
		(
			$event->children, array
			(
				new AttachmentsElement
				(
					array
					(
						Element::GROUP => 'attachments',

						AttachmentsElement::T_NODEID => $event->key,
						AttachmentsElement::T_HARD_BOND => true
					)
				)
			)
		);
	}

	static public function on_files_configblock_alter_children(Event $event, \Icybee\Modules\Files\ConfigBlock $block)
	{
		global $core;

		if (get_class($event->module) != 'Icybee\Modules\Files\Module')
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

		$event->attributes[Element::GROUPS]['attachments'] = array
		(
			'title' => 'Attachments',
			'weight' => 10
		);

		$event->children['global[nodes_attachments.scope]'] = new Element
		(
			Element::TYPE_CHECKBOX_GROUP, array
			(
				Form::LABEL => t('nodes_attachments.element.label.scope'),
				Element::OPTIONS => $scope,
				Element::GROUP => 'attachments',

				'class' => 'list combo',
				'value' => $scope_value
			)
		);
	}

	/**
	 * Alters the `nodes_attachments.scope` config property.
	 *
	 * @param Event $event
	 */
	static public function before_config_operation_properties(Event $event, \Icybee\Modules\Files\ConfigOperation $sender)
	{
		global $core;

		if (!isset($event->request->params['global']['nodes_attachments.scope']))
		{
			return;
		}

		$scope = $event->request->params['global']['nodes_attachments.scope'];

		array_walk
		(
			$scope, function(&$v)
			{
				$v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
			}
		);

		$scope = array_filter($scope);

		$event->request->params['global']['nodes_attachments.scope'] = $scope ? implode(',', array_keys($scope)) : null;
	}

	/**
	 * The `node:attachments` markup can be used to render a node attachments.
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
	 *     <p:foreach>
	 *     <p><a href="#{@url('download')}">#{@label}</a>
	 *     <span class="metas">(#{@extension} – #{@size.format_size()}</span></p>
	 *     </p:foreach>
	 *     </ul>
	 *     </div>
	 *
	 * Use the I18n scope to translate "Download attachment" or "Attachments" to module specific
	 * translations such as "Download press release" or "Press release attachments".
	 *
 	 * Attachments are created using the "resources.files.attached" module.
	 *
	 * @param array $args
	 * @param Patron\Engine $patron
	 * @param string|null $template
	 * @return string|null The rendered attached file(s), or null if no files were attached.
	 */
	static public function markup_node_attachments(array $args=array(), \Patron\Engine $patron, $template)
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

	static private function make_link(\Icybee\Modules\Files\File $file, $label=null)
	{
		if (!$label)
		{
			$label = $file->label;
		}

		return '<a href="' . \ICanBoogie\escape($file->url('download')) . '">' . \ICanBoogie\escape($label) . '</a> <span class="metas">(' . $file->extension . ' &ndash; ' . \ICanBoogie\I18n\format_size($file->size) . ')</span>';
	}
}