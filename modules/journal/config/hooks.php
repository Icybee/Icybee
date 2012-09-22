<?php

namespace Icybee\Modules\Journal;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Operation::process' => $hooks . 'on_operation_process'
	)
);