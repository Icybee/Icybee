<?php

namespace Icybee\Modules\Users\Roles;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'users',
	Module::T_DESCRIPTION => 'Role management',
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
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Roles',
	Module::T_VERSION => '1.0'
);