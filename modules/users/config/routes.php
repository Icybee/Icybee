<?php

return array
(
	'manage' => array
	(

	),

	'new' => array
	(

	),

	'edit' => array
	(

	),

	'config' => array
	(

	),

	'/admin/profile' => array
	(
		'title' => 'Profil',
		'block' => 'profile',
		'visibility' => 'auto',
		'workspace' => ''
	),

	'/admin/authenticate' => array
	(
		'title' => 'Connection',
		'block' => 'connect',
		'workspace' => '',
		'visibility' => 'auto'
	),

	'/api/nonce-login-request' => array
	(
		'class' => 'ICanBoogie\Operation\Users\NonceLoginRequest'
	),

	'/api/nonce-login-request/:email' => array
	(
		'class' => 'ICanBoogie\Operation\Users\NonceLoginRequest'
	),

	'/api/nonce-login/:email/:token' => array
	(
		'class' => 'ICanBoogie\Operation\Users\NonceLogin'
	)
);