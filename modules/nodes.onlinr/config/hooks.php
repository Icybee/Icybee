<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => 'ICanBoogie\Modules\Nodes\Onlinr\Hooks::on_alter_block_edit',

		'operation.save' => array
		(
			array('m:system.nodes.onlinr', 'event_operation_save')
		)
	)
);