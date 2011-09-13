<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\Nodes\Onlinr::on_alter_block_edit',

		'operation.save' => array
		(
			array('m:system.nodes.onlinr', 'event_operation_save')
		)
	)
);