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

class DeleteBlock extends \Icybee\DeleteBlock
{
	/**
	 * Returns the title of the record.
	 *
	 * @see Icybee.DeleteBlock::get_record_name()
	 */
	protected function get_record_name()
	{
		return $this->record->title;
	}
}