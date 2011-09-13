<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\Files::alter.block.config' => array
		(
			'ICanBoogie\Hooks\Nodes\Attachments::on_alter_block_config'
		),

		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\Nodes\Attachments::on_alter_block_edit',

		'operation.save' => array
		(
			'ICanBoogie\Hooks\Nodes\Attachments::event_operation_save'
		),

		'operation.delete' => 'ICanBoogie\Hooks\Nodes\Attachments::event_operation_delete',

		'ICanBoogie\Operation\Files\Config::process:before' => 'ICanBoogie\Hooks\Nodes\Attachments::before_operation_config',
		'ICanBoogie\Operation\Files\Config::process' => 'ICanBoogie\Hooks\Nodes\Attachments::on_operation_config'
	),

	'objects.methods' => array
	(
		'ActiveRecord\Node::__get_attachments' => 'ICanBoogie\Hooks\Nodes\Attachments::get_attachments'
	),

	'patron.markups' => array
	(
		'node:attachments' => array
		(
			'ICanBoogie\Hooks\Nodes\Attachments::markup_node_attachments'
		)
	)
);