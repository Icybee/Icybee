<?php

namespace ICanBoogie\Modules\Sites;

use ICanBoogie\Errors;
use ICanBoogie\Exception;

class StatusOperation extends \ICanBoogie\Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate(Errors $errors)
	{
		if ($this->request->is_put && $this->request['status'] === null)
		{
			throw new Exception('Missing status');
		}

		return true;
	}

	protected function process()
	{
		global $core;

		if ($this->request->is_put)
		{
			$this->record->status = $this->request['status'];
			$this->record->save();
		}

		return true;
	}
}