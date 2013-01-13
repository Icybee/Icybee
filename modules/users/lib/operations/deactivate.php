<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use ICanBoogie\I18n\FormattedString;

/**
 * Disables a user account.
 */
class DeactivateOperation extends ActivateOperation
{
	protected function process()
	{
		$record = $this->record;
		$record->is_activated = false;
		$record->save();

		$this->response->message = new FormattedString('!name account is deactivated.', array('!name' => $record->name));

		return true;
	}
}