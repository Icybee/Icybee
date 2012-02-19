<?php

namespace ICanBoogie\Modules\Contents\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Cache\Collection::alter' => __NAMESPACE__ . '::on_alter_cache_collection'
	)
);