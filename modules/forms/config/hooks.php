<?php

return array
(
	'events' => array
	(
		'alter.editors.options' => 'ICanBoogie\Hooks\Forms::event_alter_editor_options',
		'ICanBoogie\Operation::get_form' => 'ICanBoogie\Hooks\Forms::on_operation_get_form'
	),

	'patron.markups' => array
	(
		'feedback:form' => array
		(
			'ICanBoogie\Hooks\Forms::markup_form', array
			(
				'select' => array('required' => true)
			)
		)
	)
);