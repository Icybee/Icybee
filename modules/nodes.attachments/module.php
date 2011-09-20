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

	public function on_node_delete(Event $event, Operation\Nodes\Delete $sender)
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