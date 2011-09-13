<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Images',
	Module::T_DESCRIPTION => 'Images management',
	Module::T_EXTENDS => 'files',
	Module::T_CATEGORY => 'resources',
	Module::T_REQUIRED => true,

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_EXTENDS => 'files',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'width' => array('integer', 'unsigned' => true),
					'height' => array('integer', 'unsigned' => true),
					'alt' => array('varchar', 80)
				)
			)
		)
	)
);