<?php

namespace Icybee;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'routing.collect_routes:before' => $hooks . 'before_routing_collect_routes',

		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',
		'ICanBoogie\SaveOperation::control:before' => $hooks . 'before_save_operation_control',
		'ICanBoogie\HTTP\Dispatcher::alter' => $hooks . 'on_http_dispatcher_alter',
		'ICanBoogie\HTTP\Dispatcher::dispatch' => 'Icybee\StatsDecorator::on_dispatcher_dispatch',

		'Icybee\Modules\Pages\PageController::render:before' => $hooks . 'before_page_controller_render',
		'Icybee\Modules\Pages\PageController::render' => $hooks . 'on_page_controller_render',
		'Icybee\Modules\Users\LogoutOperation::process:before' => $hooks . 'before_user_logout'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::lazy_get_document' => 'Icybee\Document::get',
		'ICanBoogie\Core::lazy_get_cldr' => $hooks . 'get_cldr'
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