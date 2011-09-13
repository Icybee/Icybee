<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\System\Registry::on_alter_block_edit',
		'ICanBoogie\Module\Users::alter.block.edit' => 'ICanBoogie\Hooks\System\Registry::on_alter_block_edit',
		'ICanBoogie\Module\Sites::alter.block.edit' => 'ICanBoogie\Hooks\System\Registry::on_alter_block_edit',

		'ICanBoogie\Operation\Nodes\Save::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_save',
		'ICanBoogie\Operation\Users\Save::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_save',
		'ICanBoogie\Operation\Sites\Save::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_save',

		'ICanBoogie\Operation\Nodes\Delete::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_delete',
		'ICanBoogie\Operation\Users\Delete::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_delete',
		'ICanBoogie\Operation\Sites\Delete::process' => 'ICanBoogie\Hooks\System\Registry::on_operation_delete'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_metas' => 'ICanBoogie\Hooks\System\Registry::method_get_metas',
		'ICanBoogie\ActiveRecord\User::__get_metas' => 'ICanBoogie\Hooks\System\Registry::method_get_metas',
		'ICanBoogie\ActiveRecord\Site::__get_metas' => 'ICanBoogie\Hooks\System\Registry::method_get_metas',
		'ICanBoogie\Core::__get_registry' => 'ICanBoogie\Hooks\System\Registry::method_get_registry'
	)
);