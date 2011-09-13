<?php

return array
(
	'patron.markups' => array
	(
		'feedback:hit' => array
		(
			'ICanBoogie\Hooks\Feedback\Hits::markup_hit', array
			(
				'select' => array('expression' => true, 'required' => true)
			)
		),

		'feedback:hits' => array
		(
			'ICanBoogie\Hooks\Feedback\Hits::markup_hits', array
			(
				'constructor' => array('required' => true),
				'limit' => null
			)
		)
	)
);