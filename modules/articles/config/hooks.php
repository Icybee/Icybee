<?php

namespace Icybee\Modules\Articles;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'resources.files.path.change' => $hooks . 'resources_files_path_change' // FIXME-20120922: this event is no longer fired
	),

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

		'articles:authors' => array
		(
			$hooks . 'markup_articles_authors', array
			(
				'section' => null,
				'order' => 'asc'
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