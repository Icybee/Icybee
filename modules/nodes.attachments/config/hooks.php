<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Files\Module::alter.block.config' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::on_alter_block_config',
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::on_alter_block_edit',
		'ICanBoogie\Operation\Nodes\Save::process' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::on_node_save',
		'ICanBoogie\Operation\Nodes\Delete::process' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::on_node_delete',
		'ICanBoogie\Operation\Files\Config::process:before' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::before_operation_config',
		'ICanBoogie\Operation\Files\Config::process' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::on_operation_config'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_attachments' => 'ICanBoogie\Modules\Nodes\Attachments\Hooks::get_attachments'
	),

	'patron.markups' => array
	(
		'node:attachments' => array
		(
			'ICanBoogie\Modules\Nodes\Attachments\Hooks::markup_node_attachments'
		)
	)
);