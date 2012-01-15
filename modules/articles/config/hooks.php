<?php

return array
(
	'events' => array
	(
		'resources.files.path.change' => 'ICanBoogie\Modules\Articles\Hooks::resources_files_path_change'
	),

	'patron.markups' => array
	(
		'articles' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::markup_articles', array
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
			'ICanBoogie\Modules\Articles\Hooks::markup_articles_read', array
			(
				'section' => null,
				'order' => 'desc',
				'limit' => 0
			)
		),

		/*
		'articles:commented' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::articles_commented', array
			(
				'section' => null,
				'order' => 'desc',
				'limit' => 0
			)
		),
		*/

		'articles:authors' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::markup_articles_authors', array
			(
				'section' => null,
				'order' => 'asc'
			)
		),

		/*
		'article' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::article', array
			(
				'select' => array('expression' => true, 'required' => true),
				'relative' => null
			)
		),
		*/

		'articles:by:date' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::markup_by_date', array
			(
				'group' => null,
				'order' => 'asc',
				'start' => 0,
				'limit' => 0
			)
		),

		'articles:by:author' => array
		(
			'ICanBoogie\Modules\Articles\Hooks::markup_by_author'
		)
	)
);