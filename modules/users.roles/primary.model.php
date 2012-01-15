<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users\Roles;

use ICanBoogie\ActiveRecord\Users\Role;
use ICanBoogie\Exception;

class Model extends \ICanBoogie\ActiveRecord\Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties[Role::PERMS]))
		{
			$properties[Role::SERIALIZED_PERMS] = json_encode($properties[Role::PERMS]);
		}

		return parent::save($properties, $key, $options);
	}

	public function delete($rid)
	{
		if ($rid == 1)
		{
			throw new Exception('The role %role (%rid) cannot be delete', array('%role' => t('Visitor'), '%rid' => $rid));
		}

		// FIXME-20110709: deleted role is not removed from users records.

		return parent::delete($rid);
	}
}
