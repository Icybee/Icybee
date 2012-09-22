<?php

namespace Icybee\Modules\Files;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'resources',
	Module::T_DESCRIPTION => 'Foundation for file management',
	Module::T_EXTENDS => 'nodes',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\File',
			Model::T_EXTENDS => 'nodes',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'path' => 'varchar',
					'mime' => 'varchar',
					'size' => array('integer', 'unsigned' => true),
					'description' => 'text'
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Files',
	Module::T_VERSION => '1.0'
);