<?php

namespace ICanBoogie\Modules\Sites;

return array
(
	'events' => array
	(
		'ICanBoogie\HTTP\Dispatcher::dispatch:before' => __NAMESPACE__ . '\Hooks::before_http_dispatcher_dispatch'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_site' => __NAMESPACE__ . '\Hooks::get_node_site',
		'ICanBoogie\Core::get_site' => __NAMESPACE__ . '\Hooks::get_core_site',
		'ICanBoogie\Core::get_site_id' => __NAMESPACE__ . '\Hooks::get_core_site_id',
		'ICanBoogie\HTTP\Request\Context::get_site' => __NAMESPACE__ . '\Hooks::get_site_for_request_context',
		'ICanBoogie\HTTP\Request\Context::get_site_id' => __NAMESPACE__ . '\Hooks::get_site_id_for_request_context'
	)
);