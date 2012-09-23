<?php

namespace Icybee\Modules\Registry;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',
		'Icybee\Modules\Users\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',
		'Icybee\Modules\Sites\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',

		'ICanBoogie\Modules\Nodes\SaveOperation::process' => $hooks . 'on_operation_save',
		'Icybee\Modules\Users\SaveOperation::process' => $hooks . 'on_operation_save',
		'Icybee\Modules\Sites\SaveOperation::process' => $hooks . 'on_operation_save',

		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => $hooks . 'on_operation_delete',
		'Icybee\Modules\Users\DeleteOperation::process' => $hooks . 'on_operation_delete',
		'Icybee\Modules\Sites\DeleteOperation::process' => $hooks . 'on_operation_delete'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_metas' => $hooks . 'get_metas',
		'Icybee\Modules\Users\User::get_metas' => $hooks . 'get_metas',
		'Icybee\Modules\Sites\Site::get_metas' => $hooks . 'get_metas',
		'ICanBoogie\Core::get_registry' => $hooks . 'get_registry'
	)
);