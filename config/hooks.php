<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Users\Disconnect::process:before' => array
		(
			'Icybee\Hooks::before_user_disconnect',
		),

		'operation.components/*:before' => array
		(
			array('publisher_WdHooks', 'before_operation_components_all')
		),

		'operation.components/*' => array
		(
			array('publisher_WdHooks', 'operation_components_all')
		),

		'Icybee::nodes_load' => array
		(
			'Icybee::on_nodes_load'
		)
	)
);