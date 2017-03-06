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

use ICanBoogie\Binding\PrototypedBindings;
use ICanBoogie\Binding\Routing\ControllerBindings as RoutingBindings;
use ICanBoogie\Binding\Routing\ForwardUndefinedPropertiesToApplication;
use ICanBoogie\HTTP\AuthenticationRequired;
use ICanBoogie\HTTP\PermissionRequired;
use ICanBoogie\Module;
use ICanBoogie\Module\ControllerBindings as ModuleBindings;
use ICanBoogie\Routing\Controller;
use ICanBoogie\View\ControllerBindings as ViewBindings;

use Icybee\Modules\Users\Binding\ApplicationBindings as UserBindings;

/**
 * Base class for admin controllers.
 */
abstract class AdminController extends Controller
{
	use Controller\ActionTrait, ForwardUndefinedPropertiesToApplication;
	use PrototypedBindings;
	use RoutingBindings, ModuleBindings, UserBindings, ViewBindings;

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

	/*
	 * Actions
	 */

	protected function action_index()
	{
		$this->view->content = $this->module->getBlock('manage');
		$this->view['block_name'] = 'manage';
	}

	protected function action_new()
	{
		$this->view->content = $this->module->getBlock('edit');
		$this->view['block_name'] = 'new';
	}

	protected function action_edit($id)
	{
		$record = $this->model[$id];
		$this->assert_has_permission(Module::PERMISSION_MAINTAIN, $this->module);

		$this->view->content = $this->module->getBlock('edit', $id);
		$this->view['block_name'] = 'edit';
	}

	protected function action_config()
	{
		$this->view->content = $this->module->getBlock('config');
		$this->view['block_name'] = 'config';
	}

	protected function action_confirm_delete($id)
	{
		$record = $this->model[$id];
		$this->assert_has_permission(Module::PERMISSION_MANAGE, $this->module);

		$this->view->content = $this->module->getBlock('delete', $id);
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
