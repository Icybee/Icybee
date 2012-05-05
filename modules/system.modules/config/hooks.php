<?php

namespace ICanBoogie\Modules\System\Modules\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Modules\ActivateOperation::process' => __NAMESPACE__ . '::on_modules_activate',
		'ICanBoogie\Modules\System\Modules\DeactivateOperation::process' => __NAMESPACE__ . '::on_modules_deactivate',
	)
);