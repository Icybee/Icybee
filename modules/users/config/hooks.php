<?php

namespace Icybee\Modules\Users;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\AuthenticationRequired::rescue' => $hooks . 'on_security_exception_rescue',
		'ICanBoogie\PermissionRequired::rescue' => $hooks . 'on_security_exception_rescue',
		'ICanBoogie\Routing\Dispatcher::dispatch:before' => $hooks . 'before_routing_dispatcher_dispatch',
		'Icybee\Modules\Users\Roles\DeleteOperation::process:before' => $hooks . 'before_roles_delete',
		'Icybee\Modules\Users\WebsiteAdminNotAccessible::rescue' => $hooks . 'on_website_admin_not_accessible_rescue'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_user' => $hooks . 'get_user',
		'ICanBoogie\Core::get_user_id' => $hooks . 'get_user_id'
	),

	'patron.markups' => array
	(
		'users:form:login' => array
		(
			$hooks . 'markup_form_login'
		)
	)
);