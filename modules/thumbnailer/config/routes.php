<?php

return array
(
	'thumbnail' => array
	(
		'pattern' => '/api/thumbnail',
		'class' => 'ICanBoogie\Operation\Thumbnailer\Get',
		'via' => 'get'
	),

	'thumbnail:image' => array
	(
		'pattern' => '/api/:module/:nid/thumbnail',
		'class' => 'ICanBoogie\Operation\Thumbnailer\Thumbnail',
		'via' => 'get'
	),

	'thumbnail:image/version' => array
	(
		'pattern' => '/api/:module/:nid/thumbnails/:version',
		'class' => 'ICanBoogie\Operation\Thumbnailer\Thumbnail',
		'via' => 'get'
	)
);