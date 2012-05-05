<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Pages',
	Module::T_CATEGORY => 'site',
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
					'parentid' => 'foreign',
					'locationid' => 'foreign',
					'label' => array('varchar', 80),
					'pattern' => 'varchar',
					'weight' => array('integer', 'unsigned' => true),
					'template' => array('varchar', 32),
					'is_navigation_excluded' => array('boolean', 'indexed' => true)
				)
			)
		),

		'contents' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'pageid' => array('foreign', 'primary' => true),
					'contentid' => array('varchar', 64, 'primary' => true),
					'content' => 'text',
					'editor' => array('varchar', 32)
				)
			)
		)
	)
);