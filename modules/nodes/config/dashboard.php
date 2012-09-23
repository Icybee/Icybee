<?php

namespace Icybee\Modules\Nodes;

return array
(
	'system-nodes-now' => array
	(
		'title' => "From a glance",
		'callback' => __NAMESPACE__ . '\Module::dashboard_now',
		'column' => 0
	),

	'system-nodes-user-modified' => array
	(
		'title' => "Your last modifications",
		'callback' => __NAMESPACE__ . '\Module::dashboard_user_modified',
		'column' => 0
	)
);