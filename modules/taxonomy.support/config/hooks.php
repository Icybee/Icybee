<?php

return array
(
	'patron.markups' => array
	(
		'taxonomy:popularity' => array
		(
			array('taxonomy_support_WdMarkups', 'popularity'), array
			(
				'vocabulary' => null,
				'scope' => null,
				'scale' => null
			)
		),

		'taxonomy:nodes' => array
		(
			array('taxonomy_support_WdMarkups', 'nodes'), array
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