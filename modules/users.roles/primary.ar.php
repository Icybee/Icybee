<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Users;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Module;

class Role extends ActiveRecord
{
	const RID = 'rid';
	const NAME = 'name';
	const PERMS = 'perms';
	const SERIALIZED_PERMS = 'serialized_perms';
	const GUEST_RID = 1;
	const USER_RID = 2;

	static public $permission_levels = array
	(
		'none' => Module::PERMISSION_NONE,
		'access' => Module::PERMISSION_ACCESS,
		'create' => Module::PERMISSION_CREATE,
		'maintain' => Module::PERMISSION_MAINTAIN,
		'manage' => Module::PERMISSION_MANAGE,
		'administer' => Module::PERMISSION_ADMINISTER
	);

	public $rid;
	public $name;
	public $serialized_perms;

	protected function __get_perms()
	{
		return (array) json_decode($this->serialized_perms, true);
	}

	public function has_permission($access, $module=null)
	{
//		wd_log('has permission ? access: <em>\1</em>, module: <em>\2</em>', $access, (string) $module);

		$perms = $this->perms;

		#
		# check 'as is' for permissions like 'modify own module';
		#

		if (is_string($access))
		{
			if (isset($perms[$access]))
			{
				return true;
			}

			if (isset(self::$permission_levels[$access]))
			{
				$access = self::$permission_levels[$access];
			}
			else
			{
				#
				# the special permission is not defined in our permission
				# and since it's not a standard permission level we can
				# return false
				#

				return false;
			}
		}

		#
		# check modules based permission level
		#

		if (is_object($module))
		{
			$module = (string) $module;
		}

		if (isset($perms[$module]))
		{
			$level = $perms[$module];

			if ($level >= $access)
			{
				#
				# we return the real permission level, not 'true'
				#

				return $level;
			}
		}

		#
		# if the permission level was not defined in the module scope
		# we check the global scope
		#

		else if (isset($perms['all']))
		{
			$level = $perms['all'];

			if ($level >= $access)
			{
				#
				# we return the real permission level, not 'true'
				#

				return $level;
			}
		}

		return false;
	}
}