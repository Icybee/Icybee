<?php

return array
(
	'autoload' => array
	(
		'ICanBoogie\Modules\System\Cache\BaseOperation' => $path . 'lib/base.operation.php',
		'ICanBoogie\Modules\System\Cache\CacheInterface' => $path . 'lib/cache.interface.php',
		'ICanBoogie\Modules\System\Cache\Collection' => $path . 'lib/collection.php',
		'ICanBoogie\Modules\System\Cache\AssetsCache' => $path . 'lib/assets-cache.php',
		'ICanBoogie\Modules\System\Cache\CatalogsCache' => $path . 'lib/catalogs-cache.php',
		'ICanBoogie\Modules\System\Cache\ConfigsCache' => $path . 'lib/configs-cache.php',
		'ICanBoogie\Modules\System\Cache\ModulesCache' => $path . 'lib/modules-cache.php'
	)
);