<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Sites;

use Icybee\Modules\Sites\Site;
use ICanBoogie\Errors;

class StatusOperation extends \ICanBoogie\Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::get_controls();
	}

	protected function validate(Errors $errors)
	{
		if ($this->request->is_put)
		{
			$status = $this->request['status'];

			if ($status === null || !in_array($status, array(Site::STATUS_ONLINE, Site::STATUS_OFFLINE, Site::STATUS_UNDER_MAINTENANCE, Site::STATUS_DENIED_ACCESS)))
			{
				throw new \InvalidArgumentException('Invalid status value.');
			}
		}

		return true;
	}

	protected function process()
	{
		static $status_names = array
		(
			Site::STATUS_OFFLINE => 'offline',
			Site::STATUS_ONLINE => 'online',
			Site::STATUS_UNDER_MAINTENANCE => 'under maintenance',
			Site::STATUS_DENIED_ACCESS => 'restricted'
		);

		if ($this->request->is_put)
		{
			$status = $this->request['status'];

			$record = $this->record;
			$record->status = $status;
			$record->save();

			$this->response->message = array('The site %title is now ' . $status_names[$status] . '.', array('title' => $record->title));
		}

		return $this->record->status;
	}
}