<?php

return array
(
	'objects.methods' => array
	(
		'ICanBoogie\Core::__get_user' => 'ICanBoogie\Hooks\Users::get_user',
		'ICanBoogie\Core::__get_user_id' => 'ICanBoogie\Hooks\Users::get_user_id'
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