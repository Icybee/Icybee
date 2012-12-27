<?php

namespace Icybee\Modules\Views;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Pages\SaveOperation::process' => $hooks . 'on_page_save',
		'Icybee\Modules\Cache\Collection::collect' => $hooks . 'on_cache_collection_collect',
		'Icybee\Modules\Modules\ActivateOperation::process' => __NAMESPACE__ . '\CacheManager::revoke',
		'Icybee\Modules\Modules\DeactivateOperation::process' => __NAMESPACE__ . '\CacheManager::revoke'
	),

	'prototypes' => array
	(
		'Icybee\Modules\Nodes\Node::url' => $hooks . 'url',
		'Icybee\Modules\Nodes\Node::absolute_url' => $hooks . 'absolute_url',
		'Icybee\Modules\Nodes\Node::get_url' => $hooks . 'get_url',
		'Icybee\Modules\Nodes\Node::get_absolute_url' => $hooks . 'get_absolute_url',

		'Icybee\Modules\Users\User::url' => $hooks . 'url',
		'Icybee\Modules\Users\User::absolute_url' => $hooks . 'absolute_url',
		'Icybee\Modules\Users\User::get_url' => $hooks . 'get_url',
		'Icybee\Modules\Users\User::get_absolute_url' => $hooks . 'get_absolute_url',

		'Icybee\Modules\Sites\Site::resolve_view_target' => $hooks . 'resolve_view_target',
		'Icybee\Modules\Sites\Site::resolve_view_url' => $hooks . 'resolve_view_url',
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