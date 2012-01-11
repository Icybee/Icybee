<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Delete::process' => 'ICanBoogie\Hooks\Taxonomy\Terms::on_nodes_delete'
	),

	'patron.markups' => array
	(
		'taxonomy:terms' => array
		(
			'ICanBoogie\Hooks\Taxonomy\Terms::markup_terms', array
			(
				'vocabulary' => null,
				'constructor' => null
			)
		),

		'taxonomy:nodes' => array
		(
			'ICanBoogie\Hooks\Taxonomy\Terms::markup_nodes', array
			(
				'vocabulary' => null,
				'scope' => null,
				'term' => null,

				'by' => 'title',
				'order' => 'asc',
				'limit' => null
			)
		)
	)
);