<?php

namespace Icybee\Modules\Sites;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'site',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\Site',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'siteid' => 'serial',
					'path' => array('varchar', 80),
					'tld' => array('varchar', 16),
					'domain' => array('varchar', 80),
					'subdomain' => array('varchar', 80),
					'title' => array('varchar', 80),
					'admin_title' => array('varchar', 80),
					'model' => array('varchar', 32),
					'weight' => array('integer', 'unsigned' => true),
					'language' => array('varchar', 8),
					'nativeid' => 'foreign',
					'timezone' => array('varchar', 32), // widest is "America/Argentina/Buenos_Aires" with 30 characters
					'email' => 'varchar',
					'status' => array('integer', 'tiny'),
					'modified' => 'timestamp'
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Sites',
	Module::T_VERSION => '1.0'
);