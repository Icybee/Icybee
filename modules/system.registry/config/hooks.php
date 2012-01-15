<?php

namespace ICanBoogie\Modules\System\Registry\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',
		'ICanBoogie\Modules\Users\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',
		'ICanBoogie\Modules\Sites\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',

		'ICanBoogie\Operation\Nodes\Save::process' => __NAMESPACE__ . '::on_operation_save',
		'ICanBoogie\Operation\Users\Save::process' => __NAMESPACE__ . '::on_operation_save',
		'ICanBoogie\Operation\Sites\Save::process' => __NAMESPACE__ . '::on_operation_save',

		'ICanBoogie\Operation\Nodes\Delete::process' => __NAMESPACE__ . '::on_operation_delete',
		'ICanBoogie\Operation\Users\Delete::process' => __NAMESPACE__ . '::on_operation_delete',
		'ICanBoogie\Operation\Sites\Delete::process' => __NAMESPACE__ . '::on_operation_delete'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\ActiveRecord\User::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\ActiveRecord\Site::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\Core::__get_registry' => __NAMESPACE__ . '::method_get_registry'
	)
);