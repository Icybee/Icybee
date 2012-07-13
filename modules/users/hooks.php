<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users;

use ICanBoogie\ActiveRecord\User;
use ICanBoogie\Core;
use ICanBoogie\Operation\BeforeProcessEvent;
use ICanBoogie\Session;

class Hooks
{
	/*
	 * Events
	 */

	/**
	 * Checks if the role to be deleted is used or not.
	 *
	 * @param BeforeProcessEvent $event
	 * @param \ICanBoogie\Modules\Users\Roles\DeleteOperation $operation
	 */
	public static function before_delete_role(BeforeProcessEvent $event, \ICanBoogie\Modules\Users\Roles\DeleteOperation $operation)
	{
		global $core;

		$rid = $operation->key;
		$count = $core->models['users/has_many_roles']->find_by_rid($rid)->count;

		if (!$count)
		{
			return;
		}

		$event->errors['rid'] = t('The role %name is used by :count users.', array('name' => $operation->record->name, ':count' => $count));
	}

	/*
	 * Prototype methods
	 */

	/**
	 * Returns the user's identifier.
	 *
	 * This is the getter for the `$core->user_id` property.
	 *
	 * @param Core $core
	 *
	 * @return int|null Returns the identifier of the user or null if the user is a guest.
	 *
	 * @see \ICanBoogie\ActiveRecord\User.login()
	 */
	public static function get_user_id(Core $core)
	{
		if (!Session::exists())
		{
			return;
		}

		$session = $core->session;

		return isset($session->users['user_id']) ? $session->users['user_id'] : null;
	}

	/**
	 * Returns the user object.
	 *
	 * If the user identifier can be retrieved from the session, it is used to find the
	 * corresponding user.
	 *
	 * If no user could be found, a guest user object is returned.
	 *
	 * This is the getter for the `$core->user` property.
	 *
	 * @param Core $core
	 *
	 * @return ActiveRecord\User The user object, or guest user object.
	 */
	public static function get_user(Core $core)
	{
		$user = null;
		$uid = $core->user_id;
		$model = $core->models['users'];

		try
		{
			if ($uid)
			{
				$user = $model[$uid];
			}
		}
		catch (\Exception $e) {}

		if (!$user)
		{
			if (Session::exists())
			{
				unset($core->session->users['user_id']);
			}

			$user = new User($model);
		}

		return $user;
	}
}