<?php

return array
(
	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_site' => 'ICanBoogie\Modules\Sites\Hooks::get_node_site',
		'ICanBoogie\Core::get_site' => 'ICanBoogie\Modules\Sites\Hooks::get_core_site',
		'ICanBoogie\Core::get_site_id' => 'ICanBoogie\Modules\Sites\Hooks::get_core_site_id',
		'ICanBoogie\HTTP\Request\Context::get_site' => 'ICanBoogie\Modules\Sites\Hooks::get_site_for_request_context',
		'ICanBoogie\HTTP\Request\Context::get_site_id' => 'ICanBoogie\Modules\Sites\Hooks::get_site_id_for_request_context'
	)
);