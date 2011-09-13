<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Model\Feedback;

use ICanBoogie\ActiveRecord\Model;

class Hits extends Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		$properties += array
		(
			'last' => date('Y-m-d H:i:s')
		);

		return parent::save($properties, $key, $options);
	}
}