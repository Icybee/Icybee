<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Nodes;

use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

class Attachments extends \Icybee\Module
{
	const OPERATION_UPLOAD = 'upload';

	public function event_operation_save(Event $event, Operation\Nodes\Save $sender)
	{
		global $core;

		$params = &$sender->params;
		$nid = $event->rc['key'];

		if (empty($params['nodes_attachments']))
		{
			return;
		}

		$model = $this->model;

		$files_model = $core->models['resources.files'];
		$images_model = $core->models['images'];

		$root = $_SERVER['DOCUMENT_ROOT'];
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
					$fileid = $images_model->save
					(
						$attached_params + array
						(
							Node::SITEID => $core->site_id,
							Node::CONSTRUCTOR => 'images'
						)
					);
				}
				else
				{
					$fileid = $files_model->save
					(
						$attached_params + array
						(
							Node::SITEID => $core->site_id,
							Node::CONSTRUCTOR => 'resources.files'
						)
					);
				}

				if (!$fileid)
				{
					Debug::trigger('Unable to save file: \1', array($attached_params));

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

	public function event_operation_delete(Event $event, Operation\Nodes\Delete $sender)
	{
		$key = $event->operation->key;

		#
		# since Module\Files is an instance of Module\Nodes, we have to
		# check it first.
		#

		if ($sender instanceof Operation\Files\Delete)
		{
			#
			# delete attached on fileid
			#

			$this->model->where('fileid = ?', $key)->delete();
		}
		else
		{
			#
			# delete attached on nodeid
			#

			$this->model->where('nodeid = ?', $key)->delete();
		}
	}
}