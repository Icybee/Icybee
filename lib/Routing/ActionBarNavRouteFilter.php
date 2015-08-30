<?php

namespace Icybee\Routing;

use ICanBoogie\HTTP\Request;
use ICanBoogie\Module;
use ICanBoogie\Routing\Pattern;
use ICanBoogie\Routing\Route;
use Icybee\Modules\Users\User;

class ActionBarNavRouteFilter
{
	private $module_id;
	private $user;
	private $skip;

	public function __construct(Route $current_route, User $user)
	{
		$this->module_id = $module_id = $current_route->module;
		$this->user = $user;
		$this->skip = [

			"admin:$module_id:index",
			"admin:$module_id:create"

		];
	}

	public function __invoke(array $definition, $id)
	{
		if (empty($definition['module'])
		|| in_array($id, $this->skip)
		|| strpos($id, 'admin:') !== 0)
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
