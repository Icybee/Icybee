<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents;

class Model extends \ICanBoogie\Modules\Nodes\Model
{
	public function parseConditions(array $conditions)
	{
		// FIXME-20110709: is this still relevant ?

		list($where, $params) = parent::parseConditions($conditions);

		foreach ($conditions as $identifier => $value)
		{
			switch ($identifier)
			{
				case 'date':
				{
					$where[] = 'date = ?';
					$params[] = $value;
				}
				break;

				case 'year':
				{
					$where[] = 'YEAR(date) = ?';
					$params[] = $value;
				}
				break;

				case 'month':
				{
					$where[] = 'MONTH(date) = ?';
					$params[] = $value;
				}
				break;

				case 'is_home_excluded':
				{
					$where[] = 'is_home_excluded = ?';
					$params[] = $value;
				}
				break;
			}
		}

		return array($where, $params);
	}
}