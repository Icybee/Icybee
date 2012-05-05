<?php

return array
(
	'element.title' => array
	(
		'is_online' => "Include or exclude the record from the site"
	),

	'element.description' => array
	(
		'is_online' => "Only published records are available to visitors. However, unpublished
		records may be available to users who have permission.",

		'slug' => "The <q>slug</q> is the version of the title used in URLs. Written in lowercase,
		it contains only unaccentuated letters, numbers and hyphens. If empty when saving,
		the <q>slug</q> is automatically created from the title.",

		'siteid' => "Because you have permission, you can choose the belonging site for the
		record. A record belonging to a site inherits its language and only appears on this
		site.",

		'user' => "Because you have permission, you can choose the user owner of the record."
	),

	'title' => array
	(
		'visibility' => 'Visibility'
	),

	'label' => array
	(
		'is_online' => 'Published',
		'siteid' => 'Belonging site',
		'slug' => 'Slug',
		'title' => 'Title',
		'user' => 'User'
	),

	'manager.label' => array
	(
		'constructor' => 'Constructor',
		'created' => 'Date created',
		'is_online' => 'Published',
		'modified' => 'Date modified',
		'uid' => 'User'
	),

	'module_category.other' => 'Other',
	'module_title.nodes' => 'Nodes',

	'offline.operation' => array
	(
		'title' => 'Put records offline',
		'short_title' => 'Offline',
		'continue' => 'Put offline',
		'cancel' => "Don't put offline",

		'confirm' => array
		(
			'one' => 'Are you sure you want to put the selected record offline?',
			'other' => 'Are you sure you want to put the :count selected records offline?'
		)
	),

	'online.operation' => array
	(
		'title' => 'Put records online',
		'short_title' => 'Online',
		'continue' => 'Put online',
		'cancel' => "Don't put online",

		'confirm' => array
		(
			'one' => 'Are you sure you want to put the selected record online?',
			'other' => 'Are you sure you want to put the :count selected records online?'
		)
	),

	'option' => array
	(
		'save_mode_display' => 'Save and display'
	),

	'titleslugcombo.element' => array
	(
		'auto' => 'auto',
		'edit' => 'Click to edit',
		'fold' => 'Hide the <q>slug</q> input field',
		'reset' => 'Reset',
		'view' => 'View on website'
	)
);