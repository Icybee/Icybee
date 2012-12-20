<?php

namespace Icybee\Modules\Articles;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'patron.markups' => array
	(
		'articles' => array
		(
			$hooks . 'markup_articles', array
			(
				'by' => 'date',
				'section' => null,
				'order' => 'desc',
				'limit' => null,
				'date' => null,
				'page' => null,
				'category' => null,
				'tag' => null,
				'author' => null
			)
		),

		'articles:read' => array
		(
			$hooks . 'markup_articles_read', array
			(
				'section' => null,
				'order' => 'desc',
				'limit' => 0
			)
		),

		'articles:by:date' => array
		(
			$hooks . 'markup_by_date', array
			(
				'group' => null,
				'order' => 'asc',
				'start' => 0,
				'limit' => 0
			)
		),

		'articles:by:author' => array
		(
			$hooks . 'markup_by_author'
		)
	)
);