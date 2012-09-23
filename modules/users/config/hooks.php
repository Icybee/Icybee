<?php

namespace Icybee\Modules\Users;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\AuthenticationRequired::get_response' => $hooks . 'on_security_exception_get_response',
		'ICanBoogie\PermissionRequired::get_response' => $hooks . 'on_security_exception_get_response',
		'Icybee\Modules\Users\Roles\DeleteOperation::process:before' => $hooks . 'before_roles_delete'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_user' => $hooks . 'get_user',
		'ICanBoogie\Core::get_user_id' => $hooks . 'get_user_id'
	)
);