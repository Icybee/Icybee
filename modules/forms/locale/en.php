<?php

return array
(
	'editor_title.form' => 'Form',

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

	'group.legend' => array
	(
		'messages' => "Messages with the form",
		'notify' => "Notify options",
		'operation' => "Opération et configuration"
	),

	'description' => array
	(
		'is_notify' => "This option triggers the sending of an email when a form is posted successfully."
	),

	'manage.title' => array
	(
		'modelid' => 'Model',
		'subject' => 'Subject'
	),

	'module_title.forms' => 'Forms',

	#
	# BrickRouge\EmailComposer
	#

	'label' => array
	(
		'is_notify' => "Enable notification",
		'email_destination' => "Destination address",
		'email_from' => "Sender address",
		'email_bcc' => "Blind copy",
		'email_subject' => "Object",
		'email_template' => "Template"
	)
);