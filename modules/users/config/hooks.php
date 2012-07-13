<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Users\Roles\DeleteOperation::process:before' => 'ICanBoogie\Modules\Users\Hooks::before_delete_role'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_user' => 'ICanBoogie\Modules\Users\Hooks::get_user',
		'ICanBoogie\Core::get_user_id' => 'ICanBoogie\Modules\Users\Hooks::get_user_id'
	),

	'patron.markups' => array
	(
		'connect' => array
		(
			array('user_users_WdMarkups', 'connect')
		),

		'user' => array
		(
			array('user_users_WdMarkups', 'user'), array
			(
				'select' => array('required' => true)
			)
		)
	)
);