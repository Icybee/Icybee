<?php

return array
(
	'events' => array
	(
		'Icybee\Modules\Nodes\Module::alter.block.edit' => 'Icybee\Modules\Nodes\Onlinr\Hooks::on_alter_block_edit',

		'operation.save' => array
		(
			array('m:system.nodes.onlinr', 'event_operation_save')
		)
	)
);