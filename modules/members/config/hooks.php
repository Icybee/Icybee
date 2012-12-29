<?php

namespace Icybee\Modules\Members;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Routing\Dispatcher::dispatch:before' => $hooks . 'before_routing_dispatcher_dispatch'
	)
);
