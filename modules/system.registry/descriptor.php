<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_TITLE => 'Registry',
	Module::T_DESCRIPTION => 'Holds configuration settings for the system as well as nodes, users and sites.',
	Module::T_PERMISSION => false,
	Module::T_REQUIRED => true,

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'name' => array('varchar', 'primary' => true),
					'value' => 'text'
				)
			)
		),

		'node' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'targetid' => array('foreign', 'primary' => true),
					'name' => array('varchar', 'indexed' => true, 'primary' => true),
					'value' => 'text'
				)
			)
		),

		'user' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'targetid' => array('foreign', 'primary' => true),
					'name' => array('varchar', 'indexed' => true, 'primary' => true),
					'value' => 'text'
				)
			)
		),

		'site' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'targetid' => array('foreign', 'primary' => true),
					'name' => array('varchar', 'indexed' => true, 'primary' => true),
					'value' => 'text'
				)
			)
		)
	)
);