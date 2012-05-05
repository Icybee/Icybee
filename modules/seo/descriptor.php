<?php

use ICanBoogie\Module;

return array
(
	Module::T_TITLE => 'SEO',
	Module::T_CATEGORY => 'features',
	Module::T_PERMISSION => false,
	Module::T_DESCRIPTION => "Provides SEO to your website.",
	Module::T_REQUIRES => array
	(
		'pages' => 'x.x'
	)
);