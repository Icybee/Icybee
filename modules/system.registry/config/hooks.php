<?php

namespace ICanBoogie\Modules\System\Registry\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\EditBlock::alter_values' => __NAMESPACE__ . '::on_editblock_alter_values',
		'ICanBoogie\Modules\Users\EditBlock::alter_values' => __NAMESPACE__ . '::on_editblock_alter_values',
		'Icybee\Modules\Sites\EditBlock::alter_values' => __NAMESPACE__ . '::on_editblock_alter_values',

		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',
		'ICanBoogie\Modules\Users\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',
		'Icybee\Modules\Sites\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',

		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete',
		'ICanBoogie\Modules\Users\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete',
		'Icybee\Modules\Sites\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\ActiveRecord\User::get_metas' => __NAMESPACE__ . '::method_get_metas',
		'Icybee\Modules\Sites\Site::get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\Core::get_registry' => __NAMESPACE__ . '::method_get_registry'
	)
);