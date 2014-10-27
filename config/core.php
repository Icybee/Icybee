<?php

namespace Icybee;

$vars_path = \ICanBoogie\REPOSITORY . 'vars' . DIRECTORY_SEPARATOR;

return [

	'cache assets' => file_exists($vars_path . 'enable_assets_cache'),
	'cache views' => file_exists($vars_path . 'enable_views_cache'),

	'exception_handler' => 'Icybee\Hooks::exception_handler'

];
