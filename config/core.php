<?php

$includes = $path . 'includes' . DIRECTORY_SEPARATOR;
$operations = $includes . 'operations' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'ICanBoogie\Exception\Config' => $path . 'lib/core/exception.config.php',

		'BrickRouge\Widget' => $path . 'lib/widget/widget.php',
		'BrickRouge\Widget\TimeZone' => $includes . 'widgets/time-zone.php',

		'Icybee' => $path . '/lib/icybee.php',
		'Icybee\Accessor\Modules' => $path . 'lib/core/accessor/modules.php',
		'Icybee\ActiveRecord\Model\Constructor' => $path . 'lib/activerecord/model.constructor.php',
		'Icybee\Core' => $path . 'lib/core/core.php',
		'Icybee\Document' => $path . 'lib/element/document.php',
		'Icybee\Hooks' => $path . 'lib/hooks.php',
		'Icybee\Kses' => $includes . 'external/kses/kses.php',
		'Icybee\Manager' => $path . 'lib/widget/manager.php',
		'Icybee\Module' => $path . 'lib/core/module.php',
		'Icybee\Operation\ActiveRecord\Lock' => $path . 'lib/operation/activerecord/lock.php',
		'Icybee\Operation\ActiveRecord\Unlock' => $path . 'lib/operation/activerecord/unlock.php',
		'Icybee\Operation\ActiveRecord\Save' => $path . 'lib/operation/activerecord/save.php',
		'Icybee\Operation\ActiveRecord\Delete' => $path . 'lib/operation/activerecord/delete.php',
		'Icybee\Operation\Constructor\Save' => $path . 'lib/operation/constructor/save.php',
		'Icybee\Operation\Module\Blocks' => $path . 'lib/operation/module/blocks.php',
		'Icybee\Operation\Module\Config' => $path . 'lib/operation/module/config.php',
		'Icybee\Operation\Module\QueryOperation' => $path . 'lib/operation/module/query-operation.php',
		'Icybee\Operation\Widget\Get' => $path . 'lib/operation/widget/get.php',








		'WdSectionedForm' => $includes . 'wdsectionedform.php',

		'WdEMailNotifyElement' => $includes . 'wdemailnotifyelement.php',
		'WdManager' => $includes . 'wdmanager.php',
	),

	'config constructors' => array
	(
		'admin_routes' => array('Icybee\Hooks::synthesize_admin_routes', 'routes')
	),

	'connections' => array
	(
		'local' => array
		(
			'dsn' => 'sqlite:' . ICanBoogie\DOCUMENT_ROOT . 'repository/lib/local.sqlite'
		)
	),

	'modules' => array
	(
		$path . 'modules'
	)
);