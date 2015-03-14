<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\HTTP\Request;
use ICanBoogie\PermissionRequired;
use ICanBoogie\Routing\Controller;

/**
 * Returns a decorated block.
 *
 * The `decorate_flags` param is used to specify how the block is to be decorated. The following
 * flags are defined:
 *
 * - {@link DECORATE_WITH_BLOCK}: Decorate the component with a {@link BlockDecorator} instance.
 * - {@link DECORATE_WITH_ADMIN}: Decorate the component with a {@link AdminDecorator} instance.
 * - {@link DECORATE_WITH_DOCUMENT} Decorate the component with a {@link DocumentDecorator} instance.
 *
 * @property \ICanBoogie\Module\ModuleCollection $modules
 * @property \Icybee\Modules\Users\User $user
 */
class BlockController extends Controller
{
	const DECORATE_WITH_BLOCK = 1;
	const DECORATE_WITH_ADMIN = 2;
	const DECORATE_WITH_DOCUMENT = 4;

	protected $decorate_flags;
	protected $request;
	protected $block_name;

	public function __construct()
	{
		$this->decorate_flags = self::DECORATE_WITH_BLOCK | self::DECORATE_WITH_ADMIN | self::DECORATE_WITH_DOCUMENT;
	}

	/**
	 * If the `decorate_flags` param is defined the {@link $decorate_flags} property is updated.
	 *
	 * @inheritdoc
	 */
	protected function respond(Request $request)
	{
		$this->request = $request;
		$this->control();

		$flags = $request['decorate_flags'];

		if ($flags === null)
		{
			$flags = $this->decorate_flags;
		}

		return $this->decorate($this->get_component(), $flags);
	}

	/**
	 * Controls the user access to the block.
	 *
	 * @throws \ICanBoogie\PermissionRequired if the user doesn't have at least the
	 * {@link Module::PERMISSION_ACCESS} permission.
	 */
	protected function control()
	{
		if (!$this->control_permission(Module::PERMISSION_ACCESS))
		{
			throw new PermissionRequired;
		}
	}

	protected function control_permission($permission)
	{
		$route = $this->route;
		$module = $this->modules[$route->module];

		return $this->user->has_permission(Module::PERMISSION_ACCESS, $module);
	}

	/**
	 * Returns the component.
	 *
	 * The `getBlock()` method of the target module is used to retrieve the component.
	 *
	 * @return mixed
	 */
	protected function get_component()
	{
		$route = $this->route;
		$module = $this->modules[$route->module];
		$args = [ $route->block ];

		foreach ($this->request->path_params as $param => $value)
		{
			if (is_numeric($param))
			{
				$args[] = $value;
			}
		}

		return call_user_func_array([ $module, 'getBlock' ], $args);
	}

	/**
	 * Decorates the component.
	 *
	 * @param mixed $component The component to decorate.
	 * @param int $flags The flags describing how the component is to be decorated.
	 *
	 * @return mixed
	 */
	protected function decorate($component, $flags)
	{
		if ($flags & self::DECORATE_WITH_BLOCK)
		{
			$route = $this->route;
			$component = $this->decorate_with_block($component);
		}

		if ($flags & self::DECORATE_WITH_ADMIN)
		{
			$component = $this->decorate_with_admin($component);
		}

		if ($flags & self::DECORATE_WITH_DOCUMENT)
		{
			$component = $this->decorate_with_document($component);
		}

		return $component;
	}

	/**
	 * Decorate a component with an instance of {@link BlockDecorator}.
	 *
	 * @param mixed $component
	 *
	 * @return \Icybee\BlockDecorator
	 */
	protected function decorate_with_block($component)
	{
		$route = $this->route;

		return new BlockDecorator($component, $route->block, $route->module);
	}

	/**
	 * Decorate a component with an instance of {@link AdminDecorator}.
	 *
	 * @param mixed $component
	 *
	 * @return \Icybee\AdminDecorator
	 */
	protected function decorate_with_admin($component)
	{
		return new AdminDecorator($component);
	}

	/**
	 * Decorates a component with an instance of {@link DocumentDecorator}.
	 *
	 * @param mixed $component
	 *
	 * @return \Icybee\DocumentDecorator
	 */
	protected function decorate_with_document($component)
	{
		return new DocumentDecorator($component);
	}
}
