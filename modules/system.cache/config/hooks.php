<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Modules\ActivateOperation::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_activate',
		'ICanBoogie\Modules\System\Modules\DeactivateOperation::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_deactivate'
	)
);