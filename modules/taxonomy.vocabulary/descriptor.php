<?php

namespace Icybee\Modules\Taxonomy\Vocabulary;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'organize',
	Module::T_DESCRIPTION => 'Manage vocabulary',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\Vocabulary',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'vid' => 'serial',
					'siteid' => 'foreign',
					'vocabulary' => 'varchar',
					'vocabularyslug' => array('varchar', 80, 'indexed' => true),
					'is_tags' => 'boolean',
					'is_multiple' => 'boolean',
					'is_required' => 'boolean',

					/**
					 * Specify the weight of the element used to edit this vosabulary
					 * in the altered edit block of the constructor.
					 */

					'weight' => array('integer', 'unsigned' => true)
				)
			)
		),

		'scopes' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'vid' => array('foreign', 'primary' => true),
					'constructor' => array('varchar', 64, 'primary' => true)
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRES => array
	(
// 		'taxonomy.terms' => '1.x'
	),

	Module::T_TITLE => 'Vocabulary',
	Module::T_VERSION => '1.0'
);