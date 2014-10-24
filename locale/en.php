<?php

return array
(
	'block.title' => array
	(
		'config' => 'Config.',
		'edit' => 'Edit',
		'manage' => 'List',
		'new' => 'New'
	),

	'label' => array
	(
		'language' => 'Language'
	),

	'group.legend' => array
	(
		'advanced' => 'Advanced',
		'primary' => 'Main'
	),

	'option' => array
	(
		'save_mode_list' => 'Save and go to the list',
		'save_mode_continue' => 'Save and continue editing',
		'save_mode_new' => 'Save and edit a new record'
	),

	'operation' => array
	(
		'title' => 'Perform operation',
		'continue' => 'Continue',
		'cancel' => 'Cancel',

		'confirm' => array
		(
			'one' => 'Are you sure you want to perform this operation on the selected record?',
			'other' => 'Are you sure you want to perform this operation on the selected records?'
		),

		'done' => 'Operation done'
	),

	'delete.operation' => array
	(
		'title' => 'Delete records',
		'short_title' => 'Delete',
		'continue' => 'Delete',
		'cancel' => "Don't delete",

		'confirm' => array
		(
			'one' => 'Are you sure you want to permanently delete the selected record?',
			'other' => 'Are you sure you want to permanently delete the :count selected records?'
		)
	),

	'config.operation' => array
	(
		'done' => 'The configuration options have been saved.'
	),

	/*
	 * lib/blocks/manage.php
	 */

	'manage' => array
	(
		'column' => array
		(
			'created' => 'Date created',
			'created_at' => 'Date created',
			'updated' => 'Date updated',
			'updated_at' => 'Date updated'
		),

		'create_first' => '<strong><a href="!url">Create the first record…</a></strong>',

		'edit' => 'Edit the record',
		'edit_named' => 'Edit the record: :title',

		'records_count' => array
		(
			'none' => 'No records',
			'one' => 'One record',
			'other' => ':count records'
		),

		'records_count_with_filters' => array
		(
			'none' => 'No records match the filters',
			'one' => 'One record matches the filters',
			'other' => ':count records match the filters'
		)
	),

	#

	'module_category' => array
	(
		'dashboard' => 'Dashboard',
		'features' => 'Features',
		'feedback' => 'Feedback',
		'organize' => 'Organize'
	)
);