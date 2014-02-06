<?php

namespace Icybee;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Users\LogoutOperation::process:before' => $hooks . 'before_user_logout',
		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',
		'ICanBoogie\Routes::collect:before' => $hooks . 'before_routes_collect',
		'ICanBoogie\SaveOperation::control:before' => $hooks . 'before_save_operation_control',
		'ICanBoogie\HTTP\Dispatcher::alter' => $hooks . 'on_http_dispatcher_alter',
		'ICanBoogie\HTTP\Dispatcher::dispatch' => 'Icybee\StatsDecorator::on_dispatcher_dispatch'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_document' => 'Icybee\Document::get'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'Icybee\Document::markup_document_metas', array()
		),

		'document:css' => array
		(
			'Icybee\Document::markup_document_css', array
			(
				'href' => null,
				'weight' => 100
			)
		),

		'document:js' => array
		(
			'Icybee\Document::markup_document_js', array
			(
				'href' => null,
				'weight' => 100
			)
		),

		'document:title' => array
		(
			'Icybee\Document::markup_document_title', array()
		),

		'alerts' => array
		(
			'Icybee\Hooks::markup_alerts', array()
		),

		'body' => array
		(
			'Icybee\Hooks::markup_body', array
			(
				'class' => null
			)
		)
	)
);