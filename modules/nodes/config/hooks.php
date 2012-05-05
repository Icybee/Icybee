<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Modules\ActivateOperation::process' => 'ICanBoogie\Modules\Nodes\Hooks::on_modules_activate',
		'ICanBoogie\Modules\System\Modules\DeactivateOperation::process' => 'ICanBoogie\Modules\Nodes\Hooks::on_modules_deactivate'
	),

	'patron.markups' => array
	(
		'node' => array
		(
			'o:system_nodes_view_WdMarkup', /*array('system_nodes_WdMarkups', 'node'),*/ array
			(
				'select' => array('expression' => true, 'required' => true)
			)
		),

		'nodes' => array
		(
			'o:system_nodes_list_WdMarkup', /*array('system_nodes_WdMarkups', 'nodes'),*/ array
			(
				'select' => array('expression' => true),
				'scope' => 'nodes',
				'constructor' => null,
				'order' => 'title',
				'page' => 0,
				'limit' => 10
			)
		),

		'node:navigation' => array
		(
			'ICanBoogie\Modules\Nodes\Hooks::markup_node_navigation'
		)
	)
);