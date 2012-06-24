<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_CATEGORY => 'dashboard',
	Module::T_DESCRIPTION => 'Logs website activity into a journal',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_ACTIVERECORD_CLASS => 'Icybee\Modules\Journal\Entry',
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

	Module::T_NAMESPACE => 'Icybee\Modules\Journal',
	Module::T_TITLE => 'Journal'
);