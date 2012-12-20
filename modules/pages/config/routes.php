<?php

return array
(
	'redirect:admin/site' => array
	(
		'pattern' => '/admin/site',
		'location' => '/admin/pages'
	),

	'admin:pages/export' => array
	(
		'pattern' => '/admin/pages/export',
		'title' => 'Export',
		'block' => 'export'
	)
);