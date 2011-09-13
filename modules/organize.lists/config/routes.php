<?php

return array
(
	'manage' => array
	(

	),

	'new' => array
	(

	),

	'edit' => array
	(

	),

	'/api/widgets/adjust-nodes-list/add/<nid:\d+>' => array
	(
		'callback' => array('WdAdjustNodesListWidget', 'operation_add')
	)
);