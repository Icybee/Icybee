<?php

namespace Icybee\Modules\Pages;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_TITLE => 'Pages',
	Module::T_CATEGORY => 'site',
	Module::T_EXTENDS => 'nodes',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::EXTENDING => 'nodes',
			Model::SCHEMA => array
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
			Model::CLASSNAME => 'ICanBoogie\ActiveRecord\Model',
			Model::SCHEMA => array
			(
				'fields' => array
				(
					'pageid' => array('foreign', 'primary' => true),
					'contentid' => array('varchar', 64, 'primary' => true),
					'content' => array('text', 'long'),
					'editor' => array('varchar', 32)
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'editor' => '1.0'
	)
);