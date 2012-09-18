<?php

return array
(
	'!admin:manage' => array
	(
		'title' => 'Actifs',
		'block' => 'manage'
	),

	'admin:system.modules/inactives' => array
	(
		'pattern' => '/admin/system.modules/inactives',
		'controller' => 'Icybee\BlockController',
		'title' => 'Inactifs',
		'block' => 'inactives'
	),

	'admin:system.modules/install' => array
	(
		'pattern' => '/admin/system.modules/<[^/]+>/install',
		'controller' => 'Icybee\BlockController',
		'title' => 'Install',
		'block' => 'install',
		'visibility' => 'auto'
	),

	'redirect:admin/features' => array
	(
		'pattern' => '/admin/features',
		'location' => '/admin/system.modules'
	)
);