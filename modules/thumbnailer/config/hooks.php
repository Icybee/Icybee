<?php

namespace ICanBoogie\Modules\Thumbnailer\Hooks;

return array
(
	'events' => array
	(
		'Icybee\ConfigBlock::alter_children' => __NAMESPACE__ . '::on_configblock_alter_children',
		'Icybee\ConfigOperation::properties:before' => __NAMESPACE__ . '::before_configoperation_properties',
		'ICanBoogie\Modules\System\Cache\Collection::alter' => __NAMESPACE__ . '::on_alter_cache_collection'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Image::thumbnail' => __NAMESPACE__ . '::method_thumbnail',
		'ICanBoogie\ActiveRecord\Image::get_thumbnail' => __NAMESPACE__ . '::method_get_thumbnail',
		'ICanBoogie\Modules\System\Cache\StatOperation::stat_thumbnails' => __NAMESPACE__ . '::method_stat_cache',
		'ICanBoogie\Modules\System\Cache\ClearOperation::clear_thumbnails' => __NAMESPACE__ . '::method_clear_cache'
	)
);