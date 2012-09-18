<?php

namespace ICanBoogie\Modules\Users;

return array
(
	'events' => array
	(
		'ICanBoogie\AuthenticationRequired::get_response' => __NAMESPACE__ . '\Hooks::on_security_exception_get_response',
		'ICanBoogie\Modules\Users\Roles\DeleteOperation::process:before' => __NAMESPACE__ . '\Hooks::before_delete_role',
		'ICanBoogie\PermissionRequired::get_response' => __NAMESPACE__ . '\Hooks::on_security_exception_get_response'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_user' => __NAMESPACE__ . '\Hooks::get_user',
		'ICanBoogie\Core::get_user_id' => __NAMESPACE__ . '\Hooks::get_user_id'
	),

	'patron.markups' => array
	(
		'connect' => array
		(
			'user_users_WdMarkups::connect'
		),

		'user' => array
		(
			'user_users_WdMarkups::user', array
			(
				'select' => array('required' => true)
			)
		)
	)
);