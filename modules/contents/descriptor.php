<?php

namespace Icybee\Modules\Contents;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'contents',
	Module::T_DESCRIPTION => 'Code de base pour gérer les contenus éditoriaux',
	Module::T_EXTENDS => 'nodes',

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_EXTENDS => 'nodes',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'subtitle' => 'varchar',
					'body' => 'text',
					'excerpt' => 'text',
					'date'=> 'datetime',
					'editor' => array('varchar', 32),
					'is_home_excluded' => array('boolean', 'indexed' => true)
				)
			)
		),

		'cache' => array
		(
			Model::ACTIVERECORD_CLASS => 'ICanBoogie\ActiveRecord',
			Model::CLASSNAME => 'ICanBoogie\ActiveRecord\Model',
			Model::CONNECTION => 'local',
			Model::SCHEMA => array
			(
				'fields' => array
				(
					'nid' => array('foreign', 'primary' => true),
					'timestamp' => 'timestamp',
					'body' => 'text'
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Contents',
	Module::T_VERSION => '1.0'
);