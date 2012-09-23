<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use ICanBoogie\Core;
use ICanBoogie\HTTP\Response;
use ICanBoogie\Route;
use ICanBoogie\Operation;
use ICanBoogie\SecurityException;
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
	 * @param \Icybee\Modules\Users\Roles\DeleteOperation $operation
	 */
	static public function before_roles_delete(Operation\BeforeProcessEvent $event, \Icybee\Modules\Users\Roles\DeleteOperation $operation)
	{
		global $core;

		$rid = $operation->key;
		$count = $core->models['users/has_many_roles']->filter_by_rid($rid)->count;

		if (!$count)
		{
			return;
		}

		$event->errors['rid'] = t('The role %name is used by :count users.', array('name' => $operation->record->name, ':count' => $count));
	}

	/**
	 * Displays a login form on {@link SecurityException}.
	 *
	 * @param \ICanBoogie\Exception\GetResponseEvent $event
	 * @param SecurityException $target
	 */
	static public function on_security_exception_get_response(\ICanBoogie\Exception\GetResponseEvent $event, SecurityException $target)
	{
		global $core;

		$request = $event->request;

		if (Route::decontextualize($request->normalized_path) != '/admin/')
		{
			\ICanBoogie\log_error($target->getMessage());
		}

		$block = $core->modules['users']->getBlock('connect');

		$document = new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($block));
		$document->body->add_class('page-slug-authenticate');

		$event->response = new Response
		(
			$target->getCode(), array
			(
				'Content-Type' => 'text/html; charset=utf-8'
			),

			(string) $document
		);

		$event->stop();
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
	 * @see \Icybee\Modules\Users\User.login()
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
	 * @return User The user object, or guest user object.
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