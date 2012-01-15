<?php

namespace ICanBoogie\Modules\System\Registry\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',
		'ICanBoogie\Modules\Users\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',
		'ICanBoogie\Modules\Sites\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',

		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',
		'ICanBoogie\Modules\Users\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',
		'ICanBoogie\Modules\Sites\SaveOperation::process' => __NAMESPACE__ . '::on_operation_save',

		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete',
		'ICanBoogie\Modules\Users\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete',
		'ICanBoogie\Modules\Sites\DeleteOperation::process' => __NAMESPACE__ . '::on_operation_delete'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\ActiveRecord\User::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\ActiveRecord\Site::__get_metas' => __NAMESPACE__ . '::method_get_metas',
		'ICanBoogie\Core::__get_registry' => __NAMESPACE__ . '::method_get_registry'
	)
);