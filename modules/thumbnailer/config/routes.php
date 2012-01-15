<?php

return array
(
	'thumbnail' => array
	(
		'pattern' => '/api/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:image' => array
	(
		'pattern' => '/api/:module/:nid/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image/version' => array
	(
		'pattern' => '/api/:module/:nid/thumbnails/:version',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	)
);