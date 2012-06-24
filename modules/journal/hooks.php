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

use ICanBoogie\Debug;

use ICanBoogie\Operation;
use ICanBoogie\Operation\ProcessEvent;

class Hooks
{
	/**
	 * Adds an entry to the journal for successfully processed operations with a success message.
	 *
	 * @param ProcessEvent $event
	 * @param Operation $operation
	 */
	public static function on_operation_process(ProcessEvent $event, Operation $operation)
	{
		global $core;

		if (empty($operation->response->success))
		{
			return;
		}

		try
		{
			$core->modules['journal']->log_operation($operation);
		}
		catch (\Exception $e)
		{
			Debug::report($e);
		}
	}
}