<?php

return array
(
	'description' => array
	(
		'is_activated' => "Only users whose account has been activated can connect.",

		'password_confirm' => "If you entered a password, please confirm.",

		'password_new' => "If the field is blank when registering a new account, a password is
		generated automatically. To personalize it, enter the password.",

		'password_update' => "If you want to change your password, please enter the new one in
		this field. Otherwise, leave the field empty.",

		'roles' => "Because you have permission, you can choose the user's roles."
	),

	'users.edit.element' => array
	(
		'description.language' => "This is the language to be used for the interface."
	),

	'label' => array
	(
		'login' => 'Log in',
		'logout' => 'Log out',
		'display_as' => 'Display as',
		'email' => 'E-mail',
		'email_confirm' => 'Confirm e-mail',
		'firstname' => 'Firstname',
		'is_activated' => "The user's account is active",
		'lastconnection' => 'Date connected',
		'lastname' => 'Lastname',
		'lost_password' => 'I forgot my password',
		'name' => 'Name',
		'password' => 'Password',
		'password_confirm' => 'Confirm',
		'roles' => 'Roles',
		'timezone' => 'Timezone',
		'username' => 'Username',
		'your_email' => 'Your email address'
	),

	'manager.title' => array
	(
		'is_activated' => 'Activated'
	),

	'module_category.title.users' => 'Users',

	'activate.operation' => array
	(
		'title' => 'Activate users',
		'short_title' => 'Activate',
		'continue' => 'Activate',
		'cancel' => "Don't activate",

		'confirm' => array
		(
			'one' => 'Are you sure you want to activate the selected user?',
			'other' => 'Are you sure you want to activate the :count selected users?'
		)
	),

	'deactivate.operation' => array
	(
		'title' => 'Deactivate users',
		'short_title' => 'Deactivate',
		'continue' => 'Deactivate',
		'cancel' => "Don't deactivate",

		'confirm' => array
		(
			'one' => 'Are you sure you want to deactivate the selected user?',
			'other' => 'Are you sure you want to deactivate the :count selected users?'
		)
	),

	'nonce_login_request.operation' => array
	(
		'title' => 'Request a nonce login',
		'message' => array
		(
			'subject' => "Here's a message to help you login",
			'template' => <<<EOT
This message has been sent to help you login.

Using the following URL you'll be able to login instantly and update your password:

:url

This URL can only be used once and is only valid until :until.

If you didn't create a profile neither asked for a new password, this message might be the result
of an attack attempt on the website. If you think this is the case, please contact its admin.

The remote address of the request was: :ip.
EOT
		),

		'success' => "A message to help you login has been sent to the email address %email."
	),

	'permission.modify own profile' => "The user can modify its profile",

	'section.title' => array
	(
		'contact' => 'Contact',
		'connection' => 'Connection'
	)
);