<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Terms',
	Module::T_DESCRIPTION => 'Manage vocabulary terms',
	Module::T_CATEGORY => 'organize',

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_IMPLEMENTS => array
			(
				array('model' => 'taxonomy.vocabulary/primary')
			),

			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'vtid' => 'serial',
					'vid' => 'foreign',
					'term' => 'varchar',
					'termslug' => 'varchar',
					'weight' => array('integer', 'unsigned' => true)
				)
			)
		),

		'nodes' => array
		(
			Model::T_IMPLEMENTS => array
			(
				array('model' => 'taxonomy.terms/primary')
			),

			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'vtid' => array('foreign', 'primary' => true),
					'nid' => array('foreign', 'primary' => true),
					'weight' => array('integer', 'unsigned' => true)
				)
			)
		)
	)
);