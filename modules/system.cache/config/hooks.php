<?php

return array
(
	'events' => array
	(
		'Icybee\Modules\Modules\ActivateOperation::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_activate',
		'Icybee\Modules\Modules\DeactivateOperation::process' => 'ICanBoogie\Modules\System\Cache\Hooks::on_modules_deactivate'
	)
);