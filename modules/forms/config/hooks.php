<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation::get_form' => 'Icybee\Modules\Forms\Hooks::on_operation_get_form'
	),

	'patron.markups' => array
	(
		'feedback:form' => array
		(
			'Icybee\Modules\Forms\Hooks::markup_form', array
			(
				'select' => array('required' => true)
			)
		)
	)
);