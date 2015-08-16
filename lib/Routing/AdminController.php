<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Routing;

use ICanBoogie\HTTP\AuthenticationRequired;
use ICanBoogie\HTTP\PermissionRequired;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Module\ControllerBindings as ModuleBindings;

use Icybee\Modules\Users\User;

/**
 * Base class for admin controllers.
 *
 * @property User $user
 */
abstract class AdminController extends ResourceController
{
	use ModuleBindings;

	/**
	 * Returns name as "admin/{name}" instead of "{name}_admin".
	 *
	 * @inheritdoc
	 */
	protected function get_name()
	{
		$controller_class = get_class($this);

		if (preg_match('/(\w+)AdminController$/', $controller_class, $matches))
		{
			return 'admin/' . \ICanBoogie\underscore($matches[1]);
		}

		return parent::get_name();
	}

	/**
	 * @return null
	 */
	protected function get_template()
	{
		return null;
	}

	/**
	 * @return string
	 */
	protected function get_layout()
	{
		return 'admin';
	}

	/**
	 * @inheritdoc
	 */
	protected function is_action_method($action)
	{
		if (in_array($action, [ 'config', 'confirm-delete']))
		{
			return true;
		}

		return parent::is_action_method($action);
	}

	/*
	 * Actions
	 */

	protected function index()
	{
		$this->view->content = $this->module->getBlock('manage');
		$this->view['block_name'] = 'manage';
	}

	protected function create()
	{
		$this->view->content = $this->module->getBlock('edit');
		$this->view['block_name'] = 'create';
	}

	protected function edit($nid)
	{
		$this->view->content = $this->module->getBlock('edit', $nid);
		$this->view['block_name'] = 'edit';
	}

	protected function config()
	{
		$this->view->content = $this->module->getBlock('config');
		$this->view['block_name'] = 'config';
	}

	protected function confirm_delete($nid)
	{
		$this->view->content = $this->module->getBlock('delete', $nid);
		$this->view['block_name'] = 'delete';
	}

	/*
	 * Support
	 */

	/**
	 * Asserts that the user has a permission.
	 *
	 * @param string $permission
	 * @param mixed|null $target
	 *
	 * @throws AuthenticationRequired if user is a guest
	 * @throws PermissionRequired if users doesn't have the required permission.
	 */
	protected function assert_has_permission($permission, $target = null)
	{
		$user = $this->user;

		if ($user->is_guest)
		{
			throw new AuthenticationRequired;
		}

		if (!$user->has_permission($permission, $target))
		{
			throw new PermissionRequired;
		}
	}
}
