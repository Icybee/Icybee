<?php

namespace Icybee\Modules\Views;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Pages\SaveOperation::process' => $hooks . 'on_page_save',
		'ICanBoogie\Modules\System\Cache\Collection::alter' => $hooks . 'on_cache_collection_collect',
		'ICanBoogie\Modules\System\Modules\ActivateOperation::process' => __NAMESPACE__ . '\CacheManager::revoke',
		'ICanBoogie\Modules\System\Modules\DeactivateOperation::process' => __NAMESPACE__ . '\CacheManager::revoke'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::url' => $hooks . 'url',
		'ICanBoogie\ActiveRecord\Node::absolute_url' => $hooks . 'absolute_url',
		'ICanBoogie\ActiveRecord\Node::get_url' => $hooks . 'get_url',
		'ICanBoogie\ActiveRecord\Node::get_absolute_url' => $hooks . 'get_absolute_url',
		'ICanBoogie\ActiveRecord\Site::resolve_view_target' => $hooks . 'resolve_view_target',
		'ICanBoogie\ActiveRecord\Site::resolve_view_url' => $hooks . 'resolve_view_url',
		'ICanBoogie\Core::get_views' => __NAMESPACE__ . '\Collection::get'
	),

	'patron.markups' => array
	(
		'call-view' => array
		(
			$hooks . 'markup_call_view', array
			(
				'name' => array('required' => true)
			)
		)
	)
);