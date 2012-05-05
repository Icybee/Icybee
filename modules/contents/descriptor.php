<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Contents',
	Module::T_DESCRIPTION => 'Code de base pour gérer les contenus éditoriaux',
	Module::T_CATEGORY => 'contents',
	Module::T_EXTENDS => 'nodes',
// 	Module::T_REQUIRED => true, FIXME: true

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
			Model::T_CONNECTION => 'local',
			Model::T_SCHEMA => array
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

	Module::T_VERSION => '1.0'
);