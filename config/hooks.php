<?php

namespace Icybee;

return [

	'patron.markups' => [

		'document:metas' => [

			'Icybee\Element\Document::markup_document_metas', [ ]

		],

		'document:css' => [

			'Icybee\Element\Document::markup_document_css', [

				'href' => null,
				'weight' => 100

			]
		],

		'document:js' => [

			'Icybee\Element\Document::markup_document_js', [

				'href' => null,
				'weight' => 100

			]
		],

		'document:title' => [

			'Icybee\Element\Document::markup_document_title', [ ]

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
