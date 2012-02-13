<?php

return array
(
	'autoload' => array
	(
		'ICanBoogie\Modules\Thumbnailer\CacheManager' => $path . 'lib/cache-manager.php',
		'ICanBoogie\Modules\Thumbnailer\Thumbnail' => $path . 'lib/thumbnail.php',
		'ICanBoogie\Modules\Thumbnailer\Versions' => $path . 'lib/versions.php',

		'Brickrouge\Widget\AdjustThumbnailVersion' => $path . 'elements/adjust-thumbnail-version.php',
		'Brickrouge\Widget\PopThumbnailVersion' => $path . 'elements/pop-thumbnail-version.php',
		'Brickrouge\Widget\AdjustThumbnailOptions' => $path . 'elements/adjust-thumbnail-options.php',
	)
);