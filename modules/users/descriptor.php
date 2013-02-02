<?php

namespace Icybee\Modules\Users;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_DESCRIPTION => 'User management',
	Module::T_CATEGORY => 'users',

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\User',
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
					'nickname' => array('varchar', 32),
					'name_as' => array('integer', 'tiny'),
					'language' => array('varchar', 8),
					'timezone' => array('varchar', 32),
					'logged_at' => 'datetime',
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

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_PERMISSIONS => array
	(
		'modify own profile'
	),

	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'users.roles' => '1.0'
	),

	Module::T_TITLE => 'Users',
	Module::T_VERSION => '1.0'
);