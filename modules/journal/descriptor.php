<?php

namespace Icybee\Modules\Journal;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'dashboard',
	Module::T_DESCRIPTION => 'Logs website activity into a journal',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::CLASSNAME => 'ICanBoogie\ActiveRecord\Model',
			Model::T_ACTIVERECORD_CLASS => __NAMESPACE__ . '\Entry',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'id' => 'serial',
					'siteid' => 'foreign',
					'uid' => 'foreign',
					'type' => array('varchar', 64),
					'severity' => array('integer', 3),
					'class' => 'varchar',
					'message' => 'text',
					'variables' => 'blob',
					'link' => 'text',
					'location' => 'text',
					'referer' => 'text',
					'timestamp' => array('timestamp', 'default' => 'current_timestamp()')
				)
			)
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Journal',
	Module::T_VERSION => '0.2-dev'
);