<?php

namespace Icybee\Modules\Registry;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Nodes\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',
		'Icybee\Modules\Users\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',
		'Icybee\Modules\Sites\EditBlock::alter_values' => $hooks . 'on_editblock_alter_values',

		'Icybee\Modules\Nodes\SaveOperation::process' => $hooks . 'on_operation_save',
		'Icybee\Modules\Users\SaveOperation::process' => $hooks . 'on_operation_save',
		'Icybee\Modules\Sites\SaveOperation::process' => $hooks . 'on_operation_save',

		'Icybee\Modules\Nodes\DeleteOperation::process' => $hooks . 'on_operation_delete',
		'Icybee\Modules\Users\DeleteOperation::process' => $hooks . 'on_operation_delete',
		'Icybee\Modules\Sites\DeleteOperation::process' => $hooks . 'on_operation_delete'
	),

	'prototypes' => array
	(
		'Icybee\Modules\Nodes\Node::lazy_get_metas' => $hooks . 'get_metas',
		'Icybee\Modules\Users\User::lazy_get_metas' => $hooks . 'get_metas',
		'Icybee\Modules\Sites\Site::lazy_get_metas' => $hooks . 'get_metas',
		'ICanBoogie\Core::lazy_get_registry' => $hooks . 'get_registry'
	)
);