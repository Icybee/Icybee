<?php

namespace ICanBoogie\Modules\Nodes\Attachments\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Files\Module::alter.block.config' => __NAMESPACE__ . '::on_alter_block_config',
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => __NAMESPACE__ . '::editblock__on_alter_children',
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_node_save',
		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => __NAMESPACE__ . '::on_node_delete',
		'ICanBoogie\Modules\Files\DeleteOperation::process' => __NAMESPACE__ . '::on_file_delete',
		'ICanBoogie\Modules\Files\ConfigOperation::process:before' => __NAMESPACE__ . '::before_operation_config',
		'ICanBoogie\Modules\Files\ConfigOperation::process' => __NAMESPACE__ . '::on_operation_config'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_attachments' => __NAMESPACE__ . '::get_attachments'
	),

	'patron.markups' => array
	(
		'node:attachments' => array
		(
			__NAMESPACE__ . '::markup_node_attachments'
		)
	)
);