<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => 'ICanBoogie\Modules\Taxonomy\Terms\Hooks::on_nodes_delete'
	),

	'patron.markups' => array
	(
		'taxonomy:terms' => array
		(
			'ICanBoogie\Modules\Taxonomy\Terms\Hooks::markup_terms', array
			(
				'vocabulary' => null,
				'constructor' => null
			)
		),

		'taxonomy:nodes' => array
		(
			'ICanBoogie\Modules\Taxonomy\Terms\Hooks::markup_nodes', array
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