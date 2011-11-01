<?php

return array
(
	':admin/manage' => array
	(
		'title' => 'Actifs'
	),

	'system.modules:install' => array
	(
		'pattern' => '/admin/system.modules/<[^/]+>/install',
		'title' => 'Install',
		'block' => 'install',
		'visibility' => 'auto'
	),

	'system.modules:inactives' => array
	(
		'pattern' => '/admin/system.modules/inactives',
		'title' => 'Inactifs',
		'block' => 'inactives'
	)
);