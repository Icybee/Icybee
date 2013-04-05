<?php

namespace Icybee\Modules\Articles;

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_CATEGORY => 'contents',
	Module::T_DESCRIPTION => 'Articles management',
	Module::T_EXTENDS => 'contents',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_EXTENDS => 'contents'
		)
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'Articles',
	Module::T_VERSION => '1.0'
);