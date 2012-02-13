<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Lists',
	Module::T_DESCRIPTION => 'Organise nodes in lists',
	Module::T_CATEGORY => 'organize',

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_EXTENDS => 'nodes',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'scope' => array('varchar', 64),
					'description' => 'text'
				)
			)
		),

		'nodes' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'listid' => 'foreign',
					'nodeid' => 'foreign',
					'parentid' => 'foreign',
					'weight' => array('integer', 'unsigned' => true),
					'label' => array('varchar', 80)
				)
			),

			Model::T_ALIAS => 'lnode'
		)
	),

	Module::T_REQUIRES => array
	(
		'nodes' => '1.x'
	)
);