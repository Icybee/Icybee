<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Members;

class ManageBlock extends \Icybee\Modules\Users\ManageBlock
{
	protected function render_cell_uid($record, $property)
	{
		$rc = '';

		if ($this->photo)
		{
			$rc .= '<img src="' . $this->thumbnail('$icon') . '" class="icon" alt="' . $this->username . '" />';
		}

		return $rc .= parent::render_cell_uid($record, $property);
	}
}