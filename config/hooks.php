<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Cache\Collection::alter' => 'Icybee\Hooks::on_alter_cache_collection',
		'ICanBoogie\Modules\Users\LogoutOperation::process:before' => 'Icybee\Hooks::before_user_logout',
		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',

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
			'Icybee\Document::markup_document_metas', array()
		),

		'document:title' => array
		(
			'Icybee\Document::markup_document_title', array()
		),

		'alerts' => array
		(
			'Icybee\Hooks::markup_alerts', array()
		)
	)
);