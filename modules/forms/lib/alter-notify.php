<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

use ICanBoogie\Operation;

/**
 * Interface for forms that can alter the notify parameters.
 */
interface AlterNotify
{
	function alter_notify(NotifyParams $notify_params, Form $record, Operation\ProcessEvent $event, Operation $operation);
}
