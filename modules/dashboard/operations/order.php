<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Dashboard;

use ICanBoogie\Errors;
use ICanBoogie\Operation;

/**
 * Saves the order of the user's dashboard blocks.
 */
class OrderOperation extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate(Errors $errors)
	{
		return !empty($this->request['order']);
	}

	protected function process()
	{
		global $core;

		sleep(1);

		$core->user->metas['dashboard.order'] = json_encode($this->request['order']);

		return true;
	}
}