<?php

namespace Icybee\Modules\Taxonomy\Terms;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'organize',
	Module::T_DESCRIPTION => 'Manage vocabulary terms',

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::IMPLEMENTING => array
			(
				array('model' => 'taxonomy.vocabulary/primary')
			),

			Model::SCHEMA => array
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
			Model::ALIAS => 'term_node',
			Model::ACTIVERECORD_CLASS => 'ICanBoogie\ActiveRecord',
			Model::CLASSNAME => 'ICanBoogie\ActiveRecord\Model',
			Model::IMPLEMENTING => array
			(
				array('model' => 'taxonomy.terms/primary')
			),

			Model::SCHEMA => array
			(
				'fields' => array
				(
					'vtid' => array('foreign', 'primary' => true),
					'nid' => array('foreign', 'primary' => true),
					'weight' => array('integer', 'unsigned' => true)
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRES => array
	(
		'nodes' => '1.0',
		'taxonomy.vocabulary' => '1.0'
	),

	Module::T_TITLE => 'Terms'
);