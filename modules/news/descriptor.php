<?php

namespace Icybee\Modules\News;

use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'contents',
	Module::T_EXTENDS => 'contents',
	Module::T_MODELS => array
	(
		'primary' => 'inherit'
	),

	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_TITLE => 'News',
	Module::T_VERSION => '1.0'
);