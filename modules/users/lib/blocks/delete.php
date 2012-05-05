<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users;

class DeleteBlock extends \Icybee\DeleteBlock
{
	/**
	 * (non-PHPdoc)
	 * @see Icybee.DeleteBlock::__get_record_name()
	 */
	protected function __get_record_name()
	{
		return $this->record->name;
	}
}