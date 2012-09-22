<?php

namespace Icybee\Modules\Dashboard;

return array
(
	'events' => array
	(
		'ICanBoogie\HTTP\Dispatcher::dispatch:before' => __NAMESPACE__ . '\Hooks::before_dispatcher_dispatch'
	)
);