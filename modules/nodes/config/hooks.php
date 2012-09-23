<?php

namespace Icybee\Modules\Nodes;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Modules\ActivateOperation::process' => $hooks . 'on_modules_activate',
		'Icybee\Modules\Modules\DeactivateOperation::process' => $hooks . 'on_modules_deactivate',
		'Icybee\Modules\Users\DeleteOperation::process:before' => $hooks . 'before_delete_user'
	),

	'patron.markups' => array
	(
		'node:navigation' => array
		(
			$hooks . 'markup_node_navigation'
		)
	)
);