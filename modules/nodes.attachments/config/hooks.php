<?php

namespace Icybee\Modules\Nodes\Attachments;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Files\ConfigBlock::alter_children' => $hooks . 'on_files_configblock_alter_children',
		'Icybee\Modules\Files\ConfigOperation::properties:before' => $hooks . 'before_config_operation_properties',
		'Icybee\Modules\Files\DeleteOperation::process' => $hooks . 'on_file_delete',
		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => $hooks . 'on_node_delete',
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => $hooks . 'on_editblock_alter_children',
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => $hooks . 'on_node_save'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_attachments' => $hooks . 'get_attachments'
	),

	'patron.markups' => array
	(
		'node:attachments' => array
		(
			$hooks . 'markup_node_attachments'
		)
	)
);