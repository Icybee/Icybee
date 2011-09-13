<?php

return array
(
	'feedback-comments-last' => array
	(
		'title' => "Derniers commentaires",
		'callback' => array('ICanBoogie\Hooks\Comments', 'dashboard_last'),
		'column' => 1
	)
);