<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Users\Roles;

use ICanBoogie\ActiveRecord\Users\Role;
use ICanBoogie\Module;
use ICanBoogie\Operation;

class Permissions extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		return true;
	}

	protected function process()
	{
		global $core;

		$request = $this->request;
		$model = $this->module->model;

		foreach ($request['roles'] as $rid => $perms)
		{
			$role = $model[$rid];

			$p = array();

			foreach ($perms as $perm => $name)
			{
				if ($name == 'inherit')
				{
					continue;
				}

				if ($name == 'on')
				{
					if (isset($core->modules->descriptors[$perm]))
					{
						#
						# the module defines his permission level
						#

						$p[$perm] = $core->modules->descriptors[$perm][Module::T_PERMISSION];

						continue;
					}
					else
					{
						#
						# this is a special permission
						#

						$p[$perm] = true;

						continue;
					}
				}

				$p[$perm] = is_numeric($name) ? $name : Role::$permission_levels[$name];
			}

			$role->perms = $p;
			$role->save();
		}

		wd_log_done('Permissions has been saved.');

		return true;
	}
}