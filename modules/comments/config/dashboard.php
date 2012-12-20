<?php

namespace Icybee\Modules\Comments;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'feedback-comments-last' => array
	(
		'title' => "Last comments",
		'callback' => $hooks . 'dashboard_last',
		'column' => 1
	)
);