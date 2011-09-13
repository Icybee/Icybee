<?php

return array
(
	'events' => array
	(
		'resources.files.path.change' => array
		(
			'ICanBoogie\Hooks\Articles::resources_files_path_change'
		)
	),

	'patron.markups' => array
	(
		'articles' => array
		(
			'ICanBoogie\Hooks\Articles::markup_articles', array
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
			'ICanBoogie\Hooks\Articles::markup_articles_read', array
			(
				'section' => null,
				'order' => 'desc',
				'limit' => 0
			)
		),

		/*
		'articles:commented' => array
		(
			'ICanBoogie\Hooks\Articles::articles_commented', array
			(
				'section' => null,
				'order' => 'desc',
				'limit' => 0
			)
		),
		*/

		'articles:authors' => array
		(
			'ICanBoogie\Hooks\Articles::markup_articles_authors', array
			(
				'section' => null,
				'order' => 'asc'
			)
		),

		/*
		'article' => array
		(
			'ICanBoogie\Hooks\Articles::article', array
			(
				'select' => array('expression' => true, 'required' => true),
				'relative' => null
			)
		),
		*/

		'articles:by:date' => array
		(
			'ICanBoogie\Hooks\Articles::markup_by_date', array
			(
				'group' => null,
				'order' => 'asc',
				'start' => 0,
				'limit' => 0
			)
		),

		'articles:by:author' => array
		(
			'ICanBoogie\Hooks\Articles::markup_by_author'
		)
	)
);