<?php

$includes = $path . 'includes' . DIRECTORY_SEPARATOR;
$operations = $includes . 'operations' . DIRECTORY_SEPARATOR;

return array
(
	'ICanBoogie\Exception\Config' => $path . 'lib/core/exception.config.php',

	'Brickrouge\Section' => $path . 'lib/element/section.php',
	'Brickrouge\Widget' => $path . 'lib/widget/widget.php',
	'Brickrouge\Widget\TimeZone' => $includes . 'widgets/time-zone.php',

	'BlueTihi\Context\LoadedNodesEvent' => $path . 'lib/bluetihi/events.php',

	'Icybee\ActiveRecord\Model\Constructor' => $path . 'lib/activerecord/model.constructor.php',
	'Icybee\Core' => $path . 'lib/core/core.php',
	'Icybee\Connection' => $path . 'lib/core/accessors/connections.php',
	'Icybee\Document' => $path . 'lib/element/document.php',
	'Icybee\Element\Actionbar' => $path . 'lib/element/actionbar.php',
	'Icybee\Element\ActionbarSearch' => $path . 'lib/element/actionbar-search.php',
	'Icybee\Element\ActionbarTitle' => $path . 'lib/element/actionbar-title.php',
	'Icybee\Element\ActionbarToolbar' => $path . 'lib/element/actionbar-toolbar.php',
	'Icybee\Element\AdminMenu' => $path . 'lib/element/admin-menu.php',
	'Icybee\Element\Form' => $path . 'lib/element/form.php',
	'Icybee\Element\Group' => $path . 'lib/element/group.php',
	'Icybee\Element\Navigation' => $path . 'lib/element/navigation.php',
	'Icybee\Element\SiteMenu' => $path . 'lib/element/site-menu.php',
	'Icybee\Element\UserMenu' => $path . 'lib/element/user-menu.php',
	'Icybee\Hooks' => $path . 'lib/hooks.php',
	'Icybee\Kses' => $includes . 'external/kses/kses.php',
	'Icybee\Manager' => $path . 'lib/widget/manager.php',
	'Icybee\Module' => $path . 'lib/core/module.php',
	'Icybee\Modules' => $path . 'lib/core/accessors/modules.php',

	# controllers

	'Icybee\AdminIndexController' => $path . 'lib/controllers/admin-index.php',
	'Icybee\BlockController' => $path . 'lib/controllers/block.php',
	'Icybee\DeleteController' => $path . 'lib/controllers/delete.php',
	'Icybee\EditController' => $path . 'lib/controllers/edit.php',

	# decorator

	'Icybee\AdminDecorator' => $path . 'lib/decorators/admin.php',
	'Icybee\DocumentDecorator' => $path . 'lib/decorators/document.php',
	'Icybee\StatsDecorator' => $path . 'lib/decorators/stats.php',

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

	'Icybee\ConfigBlock' => $path . 'lib/blocks/config.php',
	'Icybee\DeleteBlock' => $path . 'lib/blocks/delete.php',
	'Icybee\EditBlock' => $path . 'lib/blocks/edit.php',
	'Icybee\InterlockBlock' => $path . 'lib/blocks/interlock.php',
	'Icybee\ManageBlock' => $path . 'lib/blocks/manage.php',
	'Icybee\FormBlock' => $path . 'lib/blocks/form.php',

	'WdEMailNotifyElement' => $includes . 'wdemailnotifyelement.php',
	'WdManager' => $includes . 'wdmanager.php',
);