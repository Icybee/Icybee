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
use ICanBoogie\Routing\Controller;

use Brickrouge\Element;

/**
 * Returns a decorated block.
 *
 * The `decorate_flags` param is used to specify how the block is to be decorated. The following
 * flags are defined:
 *
 * - {@link DECORATE_WITH_BLOCK}: Decorate the component with a {@link BlockDecorator} instance.
 * - {@link DECORATE_WITH_ADMIN}: Decorate the component with a {@link AdminDecorator} instance.
 * - {@link DECORATE_WITH_DOCUMENT} Decorate the component with a {@link DocumentDecorator} instance.
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
			throw new \ICanBoogie\PermissionRequired();
		}
	}

	protected function control_permission($permission)
	{
		global $core;

		$route = $this->route;
		$module = $core->modules[$route->module];

		return $core->user->has_permission(Module::PERMISSION_ACCESS, $module);
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
		global $core;

		$route = $this->route;
		$module = $core->modules[$route->module];
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

/**
 * Decorates a component with a _block element_.
 *
 * The component is wrapped in a `div.block.block--<name>.block--<module>--<name>` element. Where
 * `<name>` is the normalized name of the block, and `<module>` is the normalized identifier of
 * the module that created the component.
 */
class BlockDecorator extends \Brickrouge\Decorator
{
	/**
	 * Name of the block.
	 *
	 * @var string
	 */
	protected $block_name;

	/**
	 * The identifier of the module providing the block.
	 *
	 * @var string
	 */
	protected $module_id;

	/**
	 * Initialiazes the {@link $block_name} and {@link $module_id} properties.
	 *
	 * @param mixed $block The block to decorate.
	 * @param string $block_name The name of the block.
	 * @param string $module_id The itentifier of the module providing the block.
	 */
	public function __construct($block, $block_name, $module_id)
	{
		$this->block_name = $block_name;
		$this->module_id = $module_id;

		parent::__construct($block);
	}

	public function render()
	{
		$normalized_block_name = \Brickrouge\normalize($this->block_name);
		$normalized_module_id = \Brickrouge\normalize($this->module_id);

		return new Element('div', [

			Element::INNER_HTML => $this->component,

			'class' => "block block--{$normalized_block_name} block--{$normalized_module_id}--{$normalized_block_name}"

		]);
	}
}
