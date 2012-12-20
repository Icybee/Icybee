<?php

namespace Icybee\Modules\Nodes;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'system-nodes-now' => array
	(
		'title' => "From a glance",
		'callback' => $hooks . 'dashboard_now',
		'column' => 0
	),

	'system-nodes-user-modified' => array
	(
		'title' => "Your last modifications",
		'callback' => $hooks . 'dashboard_user_modified',
		'column' => 0
	)
);