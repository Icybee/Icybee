<?php

return array
(
	'system-nodes-now' => array
	(
		'title' => "From a glance",
		'callback' => 'ICanBoogie\Module\Nodes::dashboard_now',
		'column' => 0
	),

	'system-nodes-user-modified' => array
	(
		'title' => "Your last modifications",
		'callback' => 'ICanBoogie\Module\Nodes::dashboard_user_modified',
		'column' => 0
	)
);