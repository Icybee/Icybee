<?php

namespace Icybee\Routing;

use ICanBoogie\Module;
use ICanBoogie\Module\ModuleCollection;

use Icybee\Modules\Users\User;

class CreateRouteFilter
{
	/**
	 * @var ModuleCollection
	 */
	private $modules;

	/**
	 * @var User
	 */
	private $user;

	public function __construct(ModuleCollection $modules, User $user)
	{
		$this->modules = $modules;
		$this->user = $user;
	}

	public function __invoke(array $definition, $id)
	{
		if (strpos($id, 'admin:') !== 0 || !preg_match('#:create$#', $id))
		{
			return false;
		}

		$module_id = $definition['module'];

		if (!isset($this->modules[$module_id]) || !$this->user->has_permission(Module::PERMISSION_CREATE, $module_id))
		{
			return false;
		}

		return true;
	}
}
