<?php

namespace Icybee\Modules\Images;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'resources',
	Module::T_DESCRIPTION => 'Images management',
	Module::T_EXTENDS => 'files',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\Image',
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
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRES => array
	(
		'thumbnailer' => '1.0'
	),

	Module::T_TITLE => 'Images',
	Module::T_VERSION => '1.0'
);