<?php

return array
(
	'forms' => array
	(
		'edit' => array
		(
			'element.label' => array
			(
				'modelid' => 'Form model',
				'pageid' => "Page that displays the form",
				'before' => "Message before the form",
				'after' => "Message after the form",
				'complete' => "Message of thanks"
			),

			'element.description' => array
			(
				'complete' => "This is the message displayed once the form is posted successfully."
			),

			'default.complete' => 'Your message has been sent',
			'Default values' => "Default values",
			'description_notify' => "The message subject and body of the message are formatted by :link."
		),

		'permission' => array
		(
			'post form' => 'Post form'
		)
	),

	'section.title' => array
	(
		'messages' => "Messages with the form",
		'notify' => "Notify options",
		'operation' => "Opération et configuration"
	),

	'label' => array
	(
		'is_notify' => "Enable notification",
		'notify_destination' => "Destination address",
		'notify_from' => "Sender address",
		'notify_bcc' => "Blind copy",
		'notify_subject' => "Message object",
		'notify_template' => "Message template",
		'your_message' => 'Your message'
	),

	'description' => array
	(
		'is_notify' => "This option triggers the sending of an email when a form is posted successfully."
	),

	'manager.label' => array
	(
		'modelid' => 'Model',
		'subject' => 'Subject'
	)
);