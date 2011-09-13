<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module::alter.block.config' => 'ICanBoogie\Hooks\Thumbnailer::on_alter_block_config',
		'ICanBoogie\Module\System\Cache::alter.block.manage' => 'ICanBoogie\Hooks\Thumbnailer::on_alter_block_manage',
		'Icybee\Operation\Module\Config::properties:before' => 'ICanBoogie\Hooks\Thumbnailer::before_config_properties'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Image::thumbnail' => 'ICanBoogie\Hooks\Thumbnailer::method_thumbnail',
		'ICanBoogie\ActiveRecord\Image::__get_thumbnail' => 'ICanBoogie\Hooks\Thumbnailer::method_get_thumbnail',
		'ICanBoogie\Operation\System\Cache\Stat::stat_thumbnails' => 'ICanBoogie\Hooks\Thumbnailer::method_stat_cache',
		'ICanBoogie\Operation\System\Cache\Clear::clear_thumbnails' => 'ICanBoogie\Hooks\Thumbnailer::method_clear_cache'
	)
);