<?php

namespace Icybee;

use ICanBoogie\Core;

$hooks = Hooks::class . '::';

use ICanBoogie\HTTP\RequestDispatcher;
use ICanBoogie\View\View;

return [

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
