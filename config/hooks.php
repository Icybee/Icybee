<?php

return array
(
	'events' => array
	(
		'Icybee\Modules\Users\LogoutOperation::process:before' => 'Icybee\Hooks::before_user_logout',
		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',
		'ICanBoogie\Routes::collect:before' => 'Icybee\Hooks::before_routes_collect'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_document' => 'Icybee\Document::hook_get_document'
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
		),

		'render' => array
		(
			'Icybee\Hooks::markup_render', array
			(
				'select' => array('default' => 'this', 'expression' => true),
				'property' => array()
			)
		)
	)
);