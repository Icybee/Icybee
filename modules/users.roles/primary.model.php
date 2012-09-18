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

/**
 * Primary model of the Roles module (users.roles).
 */
class Model extends \ICanBoogie\ActiveRecord\Model
{
	/**
	 * If defined, the property {@link Role::PERMS} is serialized using the {@link json_encode()}
	 * function to set the property {@link Role::SERIALIZED_PERMS}.
	 *
	 * @see ICanBoogie\ActiveRecord.Model::save()
	 */
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties[Role::PERMS]))
		{
			$properties[Role::SERIALIZED_PERMS] = json_encode($properties[Role::PERMS]);
		}

		return parent::save($properties, $key, $options);
	}

	/**
	 * @throws Exception when on tries to delete the role with identifier "1".
	 *
	 * @see ICanBoogie\ActiveRecord.DatabaseTable::delete()
	 */
	public function delete($rid)
	{
		if ($rid == 1)
		{
			throw new Exception('The role %role (%rid) cannot be deleted.', array('%role' => t('Visitor'), '%rid' => $rid));
		}

		// FIXME-20110709: deleted role is not removed from users records.

		return parent::delete($rid);
	}
}
