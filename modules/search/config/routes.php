<?php

return array
(
	'search:admin' => array
	(
		'pattern' => '/admin/search',
		'block' => 'config',
		'index' => true,
		'title' => 'Config.'
	),

	'search:admin/config' => array
	(
		'pattern' => '/admin/search/config',
		'location' => '/admin/search'
	)
);