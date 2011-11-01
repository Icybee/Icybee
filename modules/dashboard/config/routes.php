<?php

return array
(
	'redirect:admin' => array
	(
		'pattern' => '/admin/',
		'location' => '/admin/dashboard'
	),

	'dashboard' => array
	(
		'pattern' => '/admin/dashboard',
		'block' => 'dashboard',
		'workspace' => 'dashboard'
	)
);