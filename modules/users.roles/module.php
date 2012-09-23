<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\Roles;

use ICanBoogie\Operation;
use ICanBoogie\Route;
use ICanBoogie\ActiveRecord\RecordNotFound;
use Icybee\Modules\Users\Users\Role;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \Icybee\Module
{
	const OPERATION_PERMISSIONS = 'permissions';

	public static $levels = array
	(
		self::PERMISSION_NONE => 'none',
		self::PERMISSION_ACCESS => 'access',
		self::PERMISSION_CREATE => 'create',
		self::PERMISSION_MAINTAIN => 'maintain',
		self::PERMISSION_MANAGE => 'manage',
		self::PERMISSION_ADMINISTER => 'administer'
	);

	/**
	 * Overrides the methods to create the "Visitor" and "User" roles.
	 *
	 * @see ICanBoogie.Module::install()
	 */
	public function install(\ICanBoogie\Errors $errors)
	{
		$rc = parent::install($errors);

		if (!$rc)
		{
			return $rc;
		}

		$model = $this->model;

		try
		{
			$this->model[1];
		}
		catch (RecordNotFound $e)
		{
			$role = Role::from
			(
				array
				(
					Role::NAME => t('Visitor')
				),

				array($model)
			);

			var_dump($role);

			$role->save();
		}

		try
		{
			$this->model[2];
		}
		catch (RecordNotFound $e)
		{
			$role = Role::from
			(
				array
				(
					Role::NAME => t('User')
				),

				array($model)
			);

			$role->save();
		}

		return $rc;
	}

	public function is_installed(\ICanBoogie\Errors $errors)
	{
		try
		{
			$this->model->find(array(1, 2));
		}
		catch (RecordNotFound $e)
		{
			if (!$e->records[1])
			{
				$errors[$this->id] = t('Visitor role is missing');
			}

			if (!$e->records[2])
			{
				$errors[$this->id] = t('User role is missing');
			}
		}
	}
}