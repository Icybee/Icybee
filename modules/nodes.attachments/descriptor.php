<?php

namespace Icybee\Modules\Nodes\Attachments;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
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
		'nodes' => '1.0'
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Fichiers attachés',
	Module::T_VERSION => '1.0'
);