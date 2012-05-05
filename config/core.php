<?php

$includes = $path . 'includes' . DIRECTORY_SEPARATOR;
$operations = $includes . 'operations' . DIRECTORY_SEPARATOR;
$vars_path = ICanBoogie\REPOSITORY . 'vars' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'ICanBoogie\Exception\Config' => $path . 'lib/core/exception.config.php',

		'Brickrouge\Section' => $path . 'lib/element/section.php',
		'Brickrouge\Widget' => $path . 'lib/widget/widget.php',
		'Brickrouge\Widget\TimeZone' => $includes . 'widgets/time-zone.php',

		'Icybee\ActiveRecord\Model\Constructor' => $path . 'lib/activerecord/model.constructor.php',
		'Icybee\Admin\Element\Actionbar' => $path . 'lib/element/actionbar.php',
		'Icybee\Admin\Element\ActionbarTitle' => $path . 'lib/element/actionbar-title.php',
		'Icybee\Admin\Element\ActionbarToolbar' => $path . 'lib/element/actionbar-toolbar.php',
		'Icybee\Admin\Element\Navigation' => $path . 'lib/element/navigation.php',
		'Icybee\Core' => $path . 'lib/core/core.php',
		'Icybee\Connection' => $path . 'lib/core/accessors/connections.php',
		'Icybee\Document' => $path . 'lib/element/document.php',
		'Icybee\Element\Form' => $path . 'lib/element/form.php',
		'Icybee\Element\Group' => $path . 'lib/element/group.php',
		'Icybee\Hooks' => $path . 'lib/hooks.php',
		'Icybee\Kses' => $includes . 'external/kses/kses.php',
		'Icybee\Manager' => $path . 'lib/widget/manager.php',
		'Icybee\Module' => $path . 'lib/core/module.php',
		'Icybee\Modules' => $path . 'lib/core/accessors/modules.php',
		'Icybee\Pagemaker' => $path . '/lib/icybee.php',

		/*
		 * Operations
		 */

		'Icybee\Operation\ActiveRecord\Lock' => $path . 'lib/operation/activerecord/lock.php',
		'Icybee\Operation\ActiveRecord\Unlock' => $path . 'lib/operation/activerecord/unlock.php',
		'Icybee\ConfigOperation' => $path . 'lib/operation/module/config.php',
		'Icybee\DeleteOperation' => $path . 'lib/operation/activerecord/delete.php',
		'Icybee\SaveOperation' => $path . 'lib/operation/activerecord/save.php',
		'Icybee\Operation\Constructor\Save' => $path . 'lib/operation/constructor/save.php',
		'Icybee\Operation\Module\Blocks' => $path . 'lib/operation/module/blocks.php',
		'Icybee\Operation\Module\QueryOperation' => $path . 'lib/operation/module/query-operation.php',
		'Icybee\Operation\Widget\Get' => $path . 'lib/operation/widget/get.php',

		'Icybee\Views' => $path . 'lib/views.php',
		'Icybee\Views\ActiveRecord\Provider' => $path . 'lib/views/provider.php',
		'Icybee\Views\CacheManager' => $path . 'lib/views/cache-manager.php',
		'Icybee\Views\Provider' => $path . 'lib/views/provider.php',
		'Icybee\Views\View' => $path . 'lib/views/view.php',

		'Icybee\ConfigBlock' => $path . 'lib/blocks/config.php',
		'Icybee\DeleteBlock' => $path . 'lib/blocks/delete.php',
		'Icybee\EditBlock' => $path . 'lib/blocks/edit.php',
		'Icybee\ManageBlock' => $path . 'lib/blocks/manage.php',
		/*
		'Icybee\EditBlock\BeforeAlterAttributesEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\BeforeAlterPropertiesEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\BeforeAlterChildrenEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\BeforeAlterActionsEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\AlterAttributesEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\AlterPropertiesEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\AlterChildrenEvent' => $path . 'lib/blocks/edit.php',
		'Icybee\EditBlock\AlterActionsEvent' => $path . 'lib/blocks/edit.php',
		*/
		'Icybee\FormBlock' => $path . 'lib/blocks/form.php',

		'WdSectionedForm' => $includes . 'wdsectionedform.php',

		'WdEMailNotifyElement' => $includes . 'wdemailnotifyelement.php',
		'WdManager' => $includes . 'wdmanager.php',
	),

	'cache assets' => file_exists($vars_path . 'enable_assets_cache'),
	'cache catalogs' => file_exists($vars_path . 'enable_catalogs_cache'),
	'cache configs' => file_exists($vars_path . 'enable_configs_cache'),
	'cache modules' => file_exists($vars_path . 'enable_modules_cache'),
	'cache views' => file_exists($vars_path . 'enable_views_cache'),

	'config constructors' => array
	(
		'admin_routes' => array('Icybee\Hooks::synthesize_admin_routes', 'routes')
	),

	'connections' => array
	(
		'local' => array
		(
			'dsn' => 'sqlite:' . ICanBoogie\REPOSITORY . 'local.sqlite'
		)
	),

	'modules' => array
	(
		$path . 'modules'
	)
);