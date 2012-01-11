<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Vocabulary',
	Module::T_DESCRIPTION => 'Manage vocabulary',
	Module::T_CATEGORY => 'organize',
	Module::T_REQUIRES => array
	(
		'taxonomy.terms' => '1.x'
	),

	Module::T_MODELS => array
	(
		'primary' => array
		(
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
	)
);