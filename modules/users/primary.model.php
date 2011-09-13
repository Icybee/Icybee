<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Model;

use ICanBoogie\ActiveRecord\User;

class Users extends \Icybee\ActiveRecord\Model\Constructor
{
	public function save(array $properties, $key=null, array $options=array())
	{
		global $core;

		if (!$key && empty($properties[User::PASSWORD]))
		{
			$properties[User::PASSWORD] = md5(uniqid());
		}

		#
		# If defined, the password is encrypted before we pass it to our super class.
		#

		unset($properties[User::PASSWORD_HASH]);

		if (!empty($properties[User::PASSWORD]))
		{
			$properties[User::PASSWORD_HASH] = User::hash_password($properties[User::PASSWORD]);
		}

		$rc = parent::save($properties, $key, $options);

		#
		# roles
		#

		if (isset($properties[User::ROLES]))
		{
			$has_many_roles = $core->models['users/has_many_roles'];

			if ($key)
			{
				$has_many_roles->find_by_uid($key)->delete();
			}

			foreach ($properties[User::ROLES] as $rid)
			{
				if ($rid == 2)
				{
					continue;
				}

				$has_many_roles->execute('INSERT {self} SET uid = ?, rid = ?', array($rc, $rid));
			}
		}

		#
		# sites
		#

		if (isset($properties[User::RESTRICTED_SITES]))
		{
			$has_many_sites = $core->models['users/has_many_sites'];

			if ($key)
			{
				$has_many_sites->find_by_uid($key)->delete();
			}

			foreach ($properties[User::RESTRICTED_SITES] as $siteid)
			{
				$has_many_sites->execute('INSERT {self} SET uid = ?, siteid = ?', array($rc, $siteid));
			}
		}

		return $rc;
	}
}