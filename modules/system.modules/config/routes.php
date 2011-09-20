<?php

return array
(
	'manage' => array
	(
		'title' => 'Actifs'
	),

	'/admin/system.modules/<[^/]+>/install' => array
	(
		'title' => 'Install',
		'block' => 'install',
		'visibility' => 'auto'
	),

	'/admin/system.modules/inactives' => array
	(
		'title' => 'Inactifs',
		'block' => 'inactives'
	)
);