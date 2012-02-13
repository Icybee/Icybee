<?php

namespace ICanBoogie\Modules\Thumbnailer\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Module::alter.block.config' => __NAMESPACE__ . '::on_alter_block_config',
		'ICanBoogie\Modules\System\Cache\Module::alter.block.manage' => __NAMESPACE__ . '::on_alter_block_manage',
		'Icybee\Operation\Module\Config::properties:before' => __NAMESPACE__ . '::before_config_properties',
		'ICanBoogie\Modules\System\Cache\Collection::alter' => __NAMESPACE__ . '::on_alter_cache_collection'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Image::thumbnail' => __NAMESPACE__ . '::method_thumbnail',
		'ICanBoogie\ActiveRecord\Image::__get_thumbnail' => __NAMESPACE__ . '::method_get_thumbnail',
		'ICanBoogie\Modules\System\Cache\StatOperation::stat_thumbnails' => __NAMESPACE__ . '::method_stat_cache',
		'ICanBoogie\Modules\System\Cache\ClearOperation::clear_thumbnails' => __NAMESPACE__ . '::method_clear_cache'
	)
);