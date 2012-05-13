<?php

return array
(
	'prototypes' => array
	(
		'ICanBoogie\Core::__get_user' => 'ICanBoogie\Modules\Users\Hooks::get_user',
		'ICanBoogie\Core::__get_user_id' => 'ICanBoogie\Modules\Users\Hooks::get_user_id'
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