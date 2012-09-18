<?php

return array
(
	'admin:search' => array
	(
		'pattern' => '/admin/search',
		'controller' => 'Icybee\BlockController',
		'block' => 'config',
		'index' => true,
		'title' => 'Config.'
	),

	'admin:search/config' => array
	(
		'pattern' => '/admin/search/config',
		'location' => '/admin/search'
	)
);