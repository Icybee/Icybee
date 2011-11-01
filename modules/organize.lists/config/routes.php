<?php

return array
(
	':admin/manage' => array
	(

	),

	':admin/new' => array
	(

	),

	':admin/edit' => array
	(

	),

	'/api/widgets/adjust-nodes-list/add/<nid:\d+>' => array
	(
		'callback' => array('WdAdjustNodesListWidget', 'operation_add')
	)
);