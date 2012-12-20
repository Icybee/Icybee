<?php

namespace Icybee\Modules\Jobs;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_DESCRIPTION => "",
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'jobid' => 'serial',
				'uid' => 'foreign',
				'token' => array('char', 64),
				'trigger_at' => 'datetime',
				'dispose_at' => 'datetime',
				'periodical' => array('string', 80),
				'serialized_worker' => 'text',
				'serialized_worker_params' => 'text'
			)
		),

		'accomplished' => array
		(
			Model::T_SCHEMA => array
			(
				'jobid' => 'foreign',
				'date' => 'datetime',
				'message' => 'text'
			)
		)
	),

	Module::T_TITLE => 'Jobs'
);