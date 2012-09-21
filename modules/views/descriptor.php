<?php

use ICanBoogie\Module;

return array
(
	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'pages' => '1.0'
	),

	Module::T_NAMESPACE => 'Icybee\Modules\Views',
	Module::T_TITLE => 'Views'
);
