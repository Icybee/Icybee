<?php

namespace Icybee\Modules\Sites;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\HTTP\Dispatcher::dispatch:before' => $hooks . 'before_http_dispatcher_dispatch'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_site' => $hooks . 'get_node_site',
		'ICanBoogie\Core::get_site' => $hooks . 'get_core_site',
		'ICanBoogie\Core::get_site_id' => $hooks . 'get_core_site_id',
		'ICanBoogie\HTTP\Request\Context::get_site' => $hooks . 'get_site_for_request_context',
		'ICanBoogie\HTTP\Request\Context::get_site_id' => $hooks . 'get_site_id_for_request_context'
	)
);