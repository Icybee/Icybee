<?php

namespace Icybee\Modules\Contents;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Cache\Collection::alter' => $hooks . 'on_alter_cache_collection'
	)
);