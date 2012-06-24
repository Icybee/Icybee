<?php

namespace Icybee\Modules\Journal;

return array
(
	'events' => array
	(
		'ICanBoogie\Operation::process' => __NAMESPACE__ . '\Hooks::on_operation_process'
	)
);