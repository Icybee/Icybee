<?php

namespace ICanBoogie\Modules\Nodes\Attachments\Hooks;

return array
(
	'events' => array
	(
		'Icybee\Modules\Files\ConfigBlock::alter_children' => __NAMESPACE__ . '::on_files_configblock_alter_children',
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => __NAMESPACE__ . '::editblock__on_alter_children',
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_node_save',
		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => __NAMESPACE__ . '::on_node_delete',
		'Icybee\Modules\Files\DeleteOperation::process' => __NAMESPACE__ . '::on_file_delete',
		'Icybee\Modules\Files\ConfigOperation::properties:before' => __NAMESPACE__ . '::before_config_operation_properties'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_attachments' => __NAMESPACE__ . '::get_attachments'
	),

	'patron.markups' => array
	(
		'node:attachments' => array
		(
			__NAMESPACE__ . '::markup_node_attachments'
		)
	)
);