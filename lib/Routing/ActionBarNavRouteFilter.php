<?php

namespace Icybee\Routing;

use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Routing\Pattern;
use ICanBoogie\Routing\Route;
use Icybee\Modules\Users\User;

class ActionBarNavRouteFilter
{
	/**
	 * @var string
	 */
	private $module_id;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var array
	 */
	private $skip;

	/**
	 * @param Route|Module\ModuleRoute $current_route
	 * @param User $user
	 */
	public function __construct(Route $current_route, User $user)
	{
		$this->module_id = $module_id = $current_route->module;
		$this->user = $user;
		$this->skip = [

			RouteMaker::ADMIN_PREFIX . $module_id . RouteMaker::SEPARATOR . RouteMaker::ACTION_INDEX,
			RouteMaker::ADMIN_PREFIX . $module_id . RouteMaker::SEPARATOR . RouteMaker::ACTION_NEW

		];
	}

	public function __invoke(array $definition, $id)
	{
		if (empty($definition['module'])
		|| in_array($id, $this->skip)
		|| strpos($id, RouteMaker::ADMIN_PREFIX) !== 0)
		{
			return false;
		}

		$module = $definition['module'];

		if ($module != $this->module_id)
		{
			return false;
		}

		$pattern = $definition['pattern'];

		if (Pattern::is_pattern($pattern))
		{
			return false;
		}

		if (!in_array(Request::METHOD_ANY, (array) $definition['via'])
		&& !in_array(Request::METHOD_GET, (array) $definition['via']))
		{
			return false;
		}

		$permission = isset($definition['permission'])
			? $definition['permission']
			: Module::PERMISSION_ACCESS;

		return $this->user->has_permission($permission, $module);
	}
}
