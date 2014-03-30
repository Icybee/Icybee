<?php

$vars_path = ICanBoogie\REPOSITORY . 'vars' . DIRECTORY_SEPARATOR;

return [

	'cache assets' => file_exists($vars_path . 'enable_assets_cache'),
	'cache catalogs' => file_exists($vars_path . 'enable_catalogs_cache'),
	'cache configs' => file_exists($vars_path . 'enable_configs_cache'),
	'cache modules' => file_exists($vars_path . 'enable_modules_cache'),
	'cache views' => file_exists($vars_path . 'enable_views_cache')

];