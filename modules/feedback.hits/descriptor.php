<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Hits',
	Module::T_DESCRIPTION => 'Counter for your resources',
	Module::T_CATEGORY => 'feedback',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'nid' => array('foreign', 'primary' => true),
					'hits' => array('integer', 'unsigned' => true, 'default' => 1),
					'first' => array('timestamp', 'default' => 'current_timestamp()'),
					'last' => array('timestamp', 'default' => 0)
				)
			)
		)
	)
);