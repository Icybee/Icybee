<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Files',
	Module::T_DESCRIPTION => 'Foundation for file management',
	Module::T_CATEGORY => 'resources',
	Module::T_EXTENDS => 'nodes',
	Module::T_REQUIRED => true,

	Module::T_MODELS => array
	(
		'primary' => array
		(
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
	)
);