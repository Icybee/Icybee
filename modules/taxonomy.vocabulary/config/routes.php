<?php

return array
(
	':admin/manage' => array
	(

	),

	':admin/new' => array
	(

	),

	':admin/edit' => array
	(

	),

	'taxonomy.vocabulary:admin/order' => array
	(
		'pattern' => '/admin/taxonomy.vocabulary/<vid:\d+>/order',
		'title' => 'Ordonner',
		'block' => 'order',
		'visibility' => 'auto',
		'workspace' => 'organize'
	)
);