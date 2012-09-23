<?php

return array
(
	'events' => array
	(
		'Icybee\Modules\Nodes\DeleteOperation::process' => 'Icybee\Modules\Taxonomy\Terms\Hooks::on_nodes_delete'
	),

	'patron.markups' => array
	(
		'taxonomy:terms' => array
		(
			'Icybee\Modules\Taxonomy\Terms\Hooks::markup_terms', array
			(
				'vocabulary' => null,
				'constructor' => null
			)
		),

		'taxonomy:nodes' => array
		(
			'Icybee\Modules\Taxonomy\Terms\Hooks::markup_nodes', array
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