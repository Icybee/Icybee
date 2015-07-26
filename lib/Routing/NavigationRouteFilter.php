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

use ICanBoogie\Module;
use ICanBoogie\Module\ModuleCollection;

use Icybee\Modules\Users\User;

/**
 * Keeps routes that can be used for navigation.
 */
class NavigationRouteFilter extends AdminRouteFilter
{
	protected $modules;
	protected $user;

	public function __construct(ModuleCollection $modules, User $user)
	{
		$this->modules = $modules;
		$this->user = $user;
	}

	public function __invoke(array $definition, $id)
	{
		if (!parent::__invoke($definition, $id))
		{
			return false;
		}

		if (!preg_match('/:index$/', $id) || empty($definition['module']))
		{
			return false;
		}

		$module_id = $definition['module'];

		if (!isset($this->modules[$module_id]))
		{
			return false;
		}

		$permission = isset($definition['permission'])
			? $definition['permission']
			: Module::PERMISSION_ACCESS;

		return $this->user->has_permission($permission, $module_id);
	}
}
