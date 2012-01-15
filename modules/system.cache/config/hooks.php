<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\System\Modules\Activate::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_activate',
		'ICanBoogie\Operation\System\Modules\Deactivate::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_deactivate'
	)
);