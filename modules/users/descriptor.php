<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Users',
	Module::T_DESCRIPTION => 'User management',
	Module::T_CATEGORY => 'users',
	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'users.roles' => 'x.x'
	),

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'uid' => 'serial',
					'constructor' => array('varchar', 64, 'indexed' => true),
					'email' => array('varchar', 64, 'unique' => true),
					'password_hash' => array('char', 40),
					'username' => array('varchar', 32, 'unique' => true),
					'firstname' => array('varchar', 32),
					'lastname' => array('varchar', 32),
					'display' => array('integer', 'tiny'),
					'language' => array('varchar', 8),
					'timezone' => array('varchar', 32),
					'lastconnection' => 'datetime',
					'created' => array('timestamp', 'default' => 'current_timestamp()'),
					'is_activated' => array('boolean', 'indexed' => true)
				)
			)
		),

		'has_many_roles' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'uid' => array('foreign', 'primary' => true),
					'rid' => array('foreign', 'primary' => true)
				)
			)
		),

		'has_many_sites' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'uid' => array('foreign', 'primary' => true),
					'siteid' => array('foreign', 'primary' => true)
				)
			)
		)
	),

	Module::T_PERMISSIONS => array
	(
		'modify own profile'
	)
);