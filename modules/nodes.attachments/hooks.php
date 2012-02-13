<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes\Attachments;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Modules;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;

class Hooks
{
	public static function on_node_save(Event $event, Modules\Nodes\SaveOperation $operation)
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
					$fileid = ActiveRecord\Image::from
					(
						$attached_params + array
						(
							ActiveRecord\Node::SITEID => $core->site_id,
							ActiveRecord\Node::CONSTRUCTOR => 'images'
						)
					)
					->save();
				}
				else
				{
					$fileid = ActiveRecord\File::from
					(
						$attached_params + array
						(
							ActiveRecord\Node::SITEID => $core->site_id,
							ActiveRecord\Node::CONSTRUCTOR => 'files'
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

					$delete_operation = Operation::decode("/api/{$file->constructor}/{$fileid}/delete");
					$delete_operation->__invoke();

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
	 * @param Modules\Nodes\DeleteOperation $operation
	 */
	public static function on_node_delete(Event $event, Modules\Nodes\DeleteOperation $operation)
	{
		global $core;

		//TODO-20120115: if the attachment is hard we should also delete assocated files.

		$core->models['nodes.attachments']->find_by_nodeid($operation->key)->delete();
	}

	/**
	 * Deletes attachment when the associated file is deleted.
	 *
	 * @param Event $event
	 * @param Modules\Files\DeleteOperation $operation
	 */
	public static function on_file_delete(Event $event, Modules\Files\DeleteOperation $operation)
	{
		global $core;

		$core->models['nodes.attachments']->find_by_fileid($operation->key)->delete();
	}

	/**
	 * Returns the attachments of the given node.
	 *
	 * @param Node $ar
	 *
	 * @return array|null An array of attachments or null if there is none.
	 */
	public static function get_attachments(ActiveRecord\Node $ar)
	{
		global $core;

		$nodes = $core->models['nodes.attachments']
		->find_by_nodeid($ar->nid)
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
	 * Alters the "edit" block to adds the "attachments" group with a WdAttachmentsElement used to
	 * manage node attachments.
	 *
	 * @param Event $event
	 */
	public static function on_alter_block_edit(Event $event, Modules\Nodes\Module $sender)
	{
		global $core;

		if ($sender instanceof Modules\Files\Module)
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
				Element::GROUPS => array
				(
					'attachments' => array
					(
						'title' => 'Attachments'
					)
				),

				Element::CHILDREN => array
				(
					new \WdAttachmentsElement
					(
						array
						(
							Element::GROUP => 'attachments',

							\WdAttachmentsElement::T_NODEID => $event->key,
							\WdAttachmentsElement::T_HARD_BOND => true
						)
					)
				)
			)
		);
	}

	public static function on_alter_block_config(Event $event, Modules\Files\Module $sender)
	{
		global $core;

		if (get_class($sender) != 'ICanBoogie\Modules\Files\Module')
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
				Element::GROUPS => array
				(
					'attachments' => array
					(
						'title' => 'Attachments',
						'weight' => 10
					)
				),

				Element::CHILDREN => array
				(
					'global[nodes_attachments.scope]' => new Element
					(
						Element::TYPE_CHECKBOX_GROUP, array
						(
							Form::LABEL => t('nodes_attachments.element.label.scope'),
							Element::OPTIONS => $scope,
							Element::GROUP => 'attachments',

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

	public static function before_operation_config(Event $event, \ICanBoogie\Modules\Files\ConfigOperation $sender)
	{
		$params = &$event->request->params;

		if (isset($params['global']['nodes_attachments.scope']))
		{
			self::$config_scope = $params['global']['nodes_attachments.scope'];
		}

		unset($params['global']['nodes_attachments.scope']);
	}

	public static function on_operation_config(Event $event, \ICanBoogie\Modules\Files\ConfigOperation $sender)
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
	static public function markup_node_attachments(array $args=array(), \WdPatron $patron, $template)
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