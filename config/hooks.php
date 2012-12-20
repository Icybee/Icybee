<?php

namespace Icybee;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Users\LogoutOperation::process:before' => $hooks . 'before_user_logout',
		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',
		'ICanBoogie\Routes::collect:before' => $hooks . 'before_routes_collect'
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

		'document:css' => array
		(
			'Icybee\Document::markup_document_css', array()
		),

		'document:js' => array
		(
			'Icybee\Document::markup_document_js', array()
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