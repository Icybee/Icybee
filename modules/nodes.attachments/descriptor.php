<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_TITLE => 'Fichiers attachés',
	Module::T_DESCRIPTION => "Permet d'attacher des fichiers à des entrées",
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'nodeid' => array('foreign', 'primary' => true),
					'fileid' => array('foreign', 'primary' => true),
					'title' => 'varchar',
					'weight' => array('integer', 'tiny', 'unsigned' => true)
				)
			)
		)
	),

	Module::T_REQUIRES => array
	(
		'nodes' => '1.x'
	)
);