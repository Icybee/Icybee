<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Users\Logout::process:before' => 'Icybee\Hooks::before_user_logout',

		'operation.components/*:before' => 'publisher_WdHooks::before_operation_components_all', // FIXME-20120108: is this still relevant ?
		'operation.components/*' => 'publisher_WdHooks::operation_components_all', // FIXME-20120108: is this still relevant ?
		'Icybee::nodes_load' => 'Icybee::on_nodes_load'
	),

	'objects.methods' => array
	(
		'ICanBoogie\Core::__get_document' => 'Icybee\Document::hook_get_document'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'IcyBee\Document::markup_document_metas', array()
		),

		'document:title' => array
		(
			'IcyBee\Document::markup_document_title', array()
		)
	)
);