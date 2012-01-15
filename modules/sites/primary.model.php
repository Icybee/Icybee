<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Sites;

class Model extends \ICanBoogie\ActiveRecord\Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties['path']))
		{
			$path = trim($properties['path'], '/');

			if ($path)
			{
				$path = '/' . $path;
			}

			$properties['path'] = $path;
		}

		return parent::save($properties, $key, $options);
	}
}