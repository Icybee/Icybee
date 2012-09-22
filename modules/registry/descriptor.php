<?php

namespace Icybee\Modules\Registry;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_DESCRIPTION => 'Holds configuration settings as well as metadatas for nodes, users and sites.',

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
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_PERMISSION => false,
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Registry',
	Module::T_VERSION => '1.0'
);