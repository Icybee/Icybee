<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Onlinr',
	Module::T_DESCRIPTION => 'Manage the online state of your nodes',
	Module::T_PERMISSION => false,
	Module::T_STARTUP => 0,

	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_CONNECTION => 'local',
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'nid' => array('foreign', 'primary' => true),
					'publicize' => array('date', 'indexed' => true),
					'privatize' => array('date', 'indexed' => true)
				)
			)
		)
	)
);