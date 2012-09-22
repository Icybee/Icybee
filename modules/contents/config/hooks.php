<?php

namespace Icybee\Modules\Contents;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Cache\Collection::collect' => $hooks . 'on_cache_collection_collect'
	)
);