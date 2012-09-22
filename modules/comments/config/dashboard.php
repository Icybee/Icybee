<?php

namespace Icybee\Modules\Comments;

return array
(
	'feedback-comments-last' => array
	(
		'title' => "Last comments",
		'callback' => __NAMESPACE__ . '\Hooks::dashboard_last',
		'column' => 1
	)
);