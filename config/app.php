<?php

namespace ICanBoogie;

$vars_path = "repository/var";

return [

	'cache assets' => file_exists($vars_path . 'enable_assets_cache'),
	'cache views' => file_exists($vars_path . 'enable_views_cache'),

	AppConfig::EXCEPTION_HANDLER => 'Icybee\Hooks::exception_handler'

];
