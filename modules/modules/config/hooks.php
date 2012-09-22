<?php

namespace Icybee\Modules\Modules;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Modules\ActivateOperation::process' => $hooks . 'revoke_caches',
		'Icybee\Modules\Modules\DeactivateOperation::process' => $hooks . 'revoke_caches',
	)
);