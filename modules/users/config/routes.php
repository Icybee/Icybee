<?php

return array
(
	'!admin:manage' => array
	(

	),

	'!admin:new' => array
	(

	),

	'!admin:edit' => array
	(

	),

	/**
	 * A route to the user's profile.
	 */
	'admin:profile' => array
	(
		'pattern' => '/admin/profile',
		'controller' => 'Icybee\BlockController',
		'title' => 'Profile',
		'block' => 'profile',
		'visibility' => 'auto'
	),

	'admin:authenticate' => array
	(
		'pattern' => '/admin/authenticate',
		'title' => 'Connection',
		'block' => 'connect',
		'visibility' => 'auto'
	),

	'api:nonce-login-request' => array
	(
		'pattern' => '/api/nonce-login-request',
		'class' => 'ICanBoogie\Modules\Users\NonceLoginRequestOperation'
	),

	'api:inline-nonce-login-request' => array
	(
		'pattern' => '/api/nonce-login-request/:email',
		'class' => 'ICanBoogie\Modules\Users\NonceLoginRequestOperation'
	),

	'api:nonce-login' => array
	(
		'pattern' => '/api/nonce-login/:email/:token',
		'class' => 'ICanBoogie\Modules\Users\NonceLoginOperation'
	)
);