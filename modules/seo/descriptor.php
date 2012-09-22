<?php

namespace Icybee\Modules\Seo;

use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_DESCRIPTION => "Provides SEO to your website.",
	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_PERMISSION => false,
	Module::T_REQUIRES => array
	(
		'pages' => 'x.x'
	),

	Module::T_TITLE => 'SEO'
);