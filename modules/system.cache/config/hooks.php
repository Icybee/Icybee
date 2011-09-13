<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\System\Modules\Activate::process' => 'ICanBoogie\Hooks\System\Cache::on_modules_activate',
		'ICanBoogie\Operation\System\Modules\Deactivate::process' => 'ICanBoogie\Hooks\System\Cache::on_modules_deactivate'
	)
);