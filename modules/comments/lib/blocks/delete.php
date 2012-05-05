<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Comments;

class DeleteBlock extends \Icybee\DeleteBlock
{
	protected function __get_record_name()
	{
		return \ICanBoogie\shorten($this->record->contents, 32, 1);
	}

	protected function render_preview(\ICanBoogie\ActiveRecord $record)
	{
		return \ICanBoogie\escape($record->contents);
	}
}
