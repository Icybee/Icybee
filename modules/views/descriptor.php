<?php

use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_DESCRIPTION => 'Allows dynamic data from modules to be displayed in content zones.',
	Module::T_REQUIRED => true,
	Module::T_REQUIRES => array
	(
		'pages' => '1.0'
	),

	Module::T_NAMESPACE => 'Icybee\Modules\Views',
	Module::T_TITLE => 'Views',
	Module::T_VERSION => '1.0'
);
