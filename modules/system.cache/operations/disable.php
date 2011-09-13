<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\System\Cache;

use Icybee;

class Disable extends Icybee\Operation\System\Cache\Base
{
	protected function process()
	{
		$cache_id = $this->key;

		if (in_array($cache_id, self::$internal))
		{
			return $this->alter_core_config(substr($cache_id, 5), false);
		}

		return $this->{$this->callback}();
	}
}