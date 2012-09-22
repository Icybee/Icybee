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

use ICanBoogie\Operation;

class Module extends \Icybee\Module
{
	const OPERATION_UPLOAD = 'upload';

	public function on_node_delete(Operation\ProcessEvent $event, \ICanBoogie\Modules\Nodes\DeleteOperation $sender)
	{
		$key = $event->operation->key;

		#
		# since Icybee\Modules\Files\Module is an instance of ICanBoogie\Modules\Nodes\Module, we
		# have to check it first.
		#

		if ($sender instanceof \Icybee\Modules\Files\DeleteOperation)
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