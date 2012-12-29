<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

use ICanBoogie\Errors;

class PatchOperation extends \ICanBoogie\Operation
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
		$status = $this->request['status'];

		if ($status !== null && !in_array($status, array(Comment::STATUS_APPROVED, Comment::STATUS_PENDING, Comment::STATUS_SPAM)))
		{
			throw new \InvalidArgumentException('Invalid status value: ' . $status);
		}

		return $errors;
	}

	protected function process()
	{
		$record = $this->record;

		#

		$status = $this->request['status'];

		if ($status)
		{
			static $status_names = array
			(
				Comment::STATUS_APPROVED => 'Approved',
				Comment::STATUS_PENDING => 'Pending',
				Comment::STATUS_SPAM => 'Spam'
			);

			$record->status = $status;
		}

		#

		$record->save();

		return true;
	}
}