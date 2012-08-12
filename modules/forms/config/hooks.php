<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation::get_form' => 'ICanBoogie\Modules\Forms\Hooks::on_operation_get_form'
	),

	'patron.markups' => array
	(
		'feedback:form' => array
		(
			'ICanBoogie\Modules\Forms\Hooks::markup_form', array
			(
				'select' => array('required' => true)
			)
		)
	)
);