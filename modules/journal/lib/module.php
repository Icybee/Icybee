<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Journal;

use ICanBoogie\Operation;

class Module extends \Icybee\Module
{
	public function log_operation(Operation $operation, $severity=Entry::SEVERITY_INFO, $link=null)
	{
		global $core;

		$siteid = $core->site_id;
		$uid = (int) $core->user_id;
		$type = 'operation';
		$class = get_class($operation);
		$message = $operation->response->message;
		$variables = null;

		if (is_array($message))
		{
			list($message, $variables) = $message;
		}

		$location = $core->request->uri;
		$referer = $core->request->referer;

		$this->model->save(array(

			'siteid' => $siteid,
			'uid' => $uid,
			'type' => $type,
			'severity' => $severity,
			'class' => $class,
			'message' => (string) $message,
			'variables' => serialize($variables),
			'location' => $location,
			'referer' => (string) $referer

		));
	}
}