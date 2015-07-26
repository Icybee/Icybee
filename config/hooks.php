<?php

namespace Icybee;

use ICanBoogie\Core;

$hooks = Hooks::class . '::';

use ICanBoogie\HTTP\RequestDispatcher;

return [

	'events' => [

		'routing.synthesize_routes:before' => $hooks . 'before_routing_collect_routes',

		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',
		'ICanBoogie\SaveOperation::control:before' => $hooks . 'before_save_operation_control',
		RequestDispatcher::class . '::alter' => $hooks . 'on_http_dispatcher_alter',
		RequestDispatcher::class . '::dispatch' => 'Icybee\StatsDecorator::on_dispatcher_dispatch',

		'Icybee\Modules\Pages\PageRenderer::render:before' => $hooks . 'before_page_renderer_render',
		'Icybee\Modules\Pages\PageRenderer::render' => $hooks . 'on_page_renderer_render',
		'Icybee\Modules\Users\LogoutOperation::process:before' => $hooks . 'before_user_logout'

	],

	'prototypes' => [

		Core::class . '::get_language' => $hooks . 'get_language',
		Core::class . '::set_language' => $hooks . 'set_language',
		Core::class . '::lazy_get_document' => 'Icybee\Document::get'

	],

	'patron.markups' => [

		'document:metas' => [

			'Icybee\Document::markup_document_metas', [ ]

		],

		'document:css' => [

			'Icybee\Document::markup_document_css', [

				'href' => null,
				'weight' => 100

			]
		],

		'document:js' => [

			'Icybee\Document::markup_document_js', [

				'href' => null,
				'weight' => 100

			]
		],

		'document:title' => [

			'Icybee\Document::markup_document_title', [ ]

		],

		'alerts' => [

			'Icybee\Hooks::markup_alerts', [ ]

		],

		'body' => [

			'Icybee\Hooks::markup_body', [

				'class' => null

			]
		]
	]
];
