<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Roles',
	Module::T_DESCRIPTION => 'Role management',
	Module::T_CATEGORY => 'users',
	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'users' => 'x.x'
	),

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'rid' => 'serial',
					'name' => array('varchar', 32, 'unique' => true),
					'serialized_perms' => 'text'
				)
			)
		)
	)
);