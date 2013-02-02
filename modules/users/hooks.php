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
use ICanBoogie\Debug;
use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\HTTP\Request;
use ICanBoogie\HTTP\Response;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\Operation;
use ICanBoogie\PermissionRequired;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Route;
use ICanBoogie\SecurityException;
use ICanBoogie\Session;

use Icybee\AdminDecorator;
use Icybee\DocumentDecorator;

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

		$event->errors['rid'] = new FormattedString('The role %name is used by :count users.', array('name' => $operation->record->name, ':count' => $count));
	}

	/**
	 * Displays a login form on {@link SecurityException}.
	 *
	 * @param \ICanBoogie\Exception\RescueEvent $event
	 * @param SecurityException $target
	 */
	static public function on_security_exception_rescue(\ICanBoogie\Exception\RescueEvent $event, SecurityException $target)
	{
		global $core;

		$request = $event->request;

		if ($request->context->dispatcher instanceof \ICanBoogie\OperationDispatcher && $request->is_xhr)
		{
			return;
		}

		if ($target instanceof PermissionRequired || \ICanBoogie\Routing\decontextualize($request->normalized_path) != '/admin/')
		{
			\ICanBoogie\log_error($target->getMessage());
		}

		$block = $core->modules['users']->getBlock('connect');

		$document = new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($block));
		$document->body->add_class('page-slug-authenticate');

		$event->response = new Response
		(
			(string) $document, $target->getCode(), array
			(
				'Content-Type' => 'text/html; charset=utf-8'
			)
		);

		$event->stop();
	}

	/**
	 * Displays an _available websites_ form on {@link WebsiteAdminNotAccessible}.
	 *
	 * @param \ICanboogie\Exception\RescueEvent $event
	 * @param WebsiteAdminNotAccessible $target
	 */
	static public function on_website_admin_not_accessible_rescue(\ICanboogie\Exception\RescueEvent $event, WebsiteAdminNotAccessible $target)
	{
		global $core;

		$block = $core->modules['users']->getBlock('available-sites');

		$document = new \Icybee\DocumentDecorator(new \Icybee\AdminDecorator($block));

		$event->response = new Response
		(
			(string) $document, $target->getCode(), array
			(
				'Content-Type' => 'text/html; charset=utf-8'
			)
		);

		$event->stop();
	}

	/**
	 * The {@link PermissionRequired} exception is thrown if a member attempts to enter the admin.
	 *
	 * Authenticated users who don't have access to the admin of a website are redirected to the
	 * `/admin/pofile/sites` URL, in which case the `response` property of the event is altered
	 * with a {@link RedirectResponse}.
	 *
	 * @param \ICanBoogie\HTTP\Dispatcher\BeforeDispatchEvent $event
	 * @param \ICanBoogie\HTTP\Dispatcher $target
	 *
	 * @throws PermissionRequired if a member attempt to enter the admin.
	 * @throws WebsiteAdminNotAccessible if a user attempts to access the admin of a website he
	 * doesn't have access to.
	 */
	static public function before_routing_dispatcher_dispatch(\ICanBoogie\Routing\Dispatcher\BeforeDispatchEvent $event, \ICanBoogie\Routing\Dispatcher $target)
	{
		global $core;

		$path = $event->request->decontextualized_path;

		if (strpos($path, '/admin/') !== 0)
		{
			return;
		}

		$user = $core->user;

		if ($user->is_guest || $user instanceof \Icybee\Modules\Members\Member)
		{
			throw new PermissionRequired();
		}

		if (strpos($path, '/admin/profile/sites') === 0)
		{
			return;
		}

		$restricted_sites = null;

		try
		{
			$restricted_sites = $user->restricted_sites_ids;
		}
		catch (PropertyNotDefined $e)
		{
			throw $e;
		}
		catch (\Exception $e) { }

		if (!$restricted_sites || in_array($core->site_id, $restricted_sites))
		{
			return;
		}

		throw new WebsiteAdminNotAccessible();
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
	static public function get_user_id(Core $core)
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
	static public function get_user(Core $core)
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

	/*
	 * Markups
	 */

	static public function markup_form_login(array $args, $engine, $template)
	{
		$form = new LoginForm();

		return $template ? $engine($template, $form) : $form;
	}

	/**
	 * Retrieves the current user.
	 *
	 * <pre>
	 * <p:user>
	 * <!-- Content: with-param*, template -->
	 * </p:user>
	 * </pre>
	 *
	 * @param array $args
	 * @param \Patron\Engine $engine
	 * @param array $template
	 */
	static public function markup_user(array $args, $engine, $template)
	{
		global $core;

		return $engine($template, $core->user);
	}
}