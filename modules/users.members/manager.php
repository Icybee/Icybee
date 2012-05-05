<?php

namespace Icybee\Manager\Users;

use Icybee\Manager;

class Members extends Manager\Users
{
	protected function get_cell_uid($entry, $tag)
	{
		$rc = '';

		if ($this->photo)
		{
			$rc .= '<img src="' . $this->thumbnail('$icon') . '" class="icon" alt="' . $this->username . '" />';
		}

		return $rc .= parent::get_cell_uid($entry, $tag);
	}
}