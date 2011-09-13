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
		'visibility' => 'auto',
		'workspace' => 'system'
	),

	'/admin/system.modules/inactives' => array
	(
		'title' => 'Inactifs',
		'block' => 'inactives',
		'workspace' => 'system'
	)
);