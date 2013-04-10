<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\Event;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

/**
 * Nodes module.
 */
class Module extends \Icybee\Module
{
	const PERMISSION_MODIFY_BELONGING_SITE = 'modify belonging site';

	/**
	 * Defines the "view", "list" and "home" views.
	 */
	protected function get_views()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_views(), array
			(
				'view' => array
				(
					'title' => "Record detail",
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_ONE
				),

				'list' => array
				(
					'title' => 'Records list',
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
				),

				'home' => array
				(
					'title' => 'Records home',
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
				)
			)
		);
	}

	protected function resolve_primary_model_tags($tags)
	{
		return parent::resolve_model_tags($tags, 'primary') + array
		(
			Model::T_CONSTRUCTOR => $this->id
		);
	}

	static public function create_default_routes()
	{
		global $core;

		$routes = array();
		$modules = $core->modules;

		foreach ($modules->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if ($module_id == 'nodes' || $module_id == 'contents' || !$modules->is_extending($module_id, 'nodes'))
			{
				continue;
			}

			$common = array
			(
				'module' => $module_id,
				'controller' => 'Icybee\BlockController',
				'visibility' => 'visible'
			);

// 			\ICanBoogie\log("create default routes for $module_id");

			# manage (index)

			$routes["admin:$module_id"] = array
			(
				'pattern' => "/admin/$module_id",
				'title' => '.manage',
				'block' => 'manage',
				'index' => true
			)

			+ $common;

			if ($module_id == 'contents' || $modules->is_extending($module_id, 'contents') || $module_id == 'files' || $modules->is_extending($module_id, 'files'))
			{
				# config'

				$routes["admin:$module_id/config"] = array
				(
					'pattern' => "/admin/$module_id/config",
					'title' => '.config',
					'block' => 'config',
					'permission' => self::PERMISSION_ADMINISTER,
				)

				+ $common;
			}

			# create

			$routes["admin:$module_id/new"] = array
			(
				'pattern' => "/admin/$module_id/new",
				'title' => '.new',
				'block' => 'edit'
			)

			+ $common;

			# edit

			$routes["admin:$module_id/edit"] = array
			(
				'pattern' => "/admin/$module_id/<\d+>/edit",
				'controller' => 'Icybee\EditController',
				'title' => '.edit',
				'block' => 'edit',
				'visibility' => 'auto'
			)

			+ $common;

			# delete

			$routes["admin:$module_id/delete"] = array
			(
				'pattern' => "/admin/$module_id/<\d+>/delete",
				'controller' => 'Icybee\DeleteController',
				'title' => '.delete',
				'block' => 'delete',
				'visibility' => 'auto'
			)

			+ $common;
		}

		new Module\CreateDefaultRoutesEvent($modules['nodes'], array('routes' => &$routes));

// 		var_dump($routes);

		$export = var_export($routes,true);

		$core->vars['default_nodes_routes'] = "<?php\n\nreturn " . $export . ';';
	}
}

namespace Brickrouge\Element\Nodes;

class Pager extends \Brickrouge\Pager
{
	protected function getURL($n)
	{
		return '#' . $n;
	}
}

namespace Icybee\Modules\Nodes\Module;

/**
 * Event class for the `Icybee\Modules\Nodes\Module::create_default_routes` event.
 */
class CreateDefaultRoutesEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the default routes.
	 *
	 * @var array[string]array
	 */
	public $routes;

	/**
	 * The event is created with the type `create_default_routes`.
	 *
	 * @param \Icybee\Modules\Nodes\Module $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Modules\Nodes\Module $target, array $payload)
	{
		parent::__construct($target, 'create_default_routes', $payload);
	}
}