<?php

return array
(
	'/api/thumbnail' => array
	(
		'class' => 'ICanBoogie\Operation\Thumbnailer\Get',
		'via' => 'GET'
	),

	'/api/:module/:nid/thumbnail' => array
	(
		'class' => 'ICanBoogie\Operation\Thumbnailer\Thumbnail',
		'via' => 'GET'
	),

	'/api/:module/:nid/thumbnails/:version' => array
	(
		'class' => 'ICanBoogie\Operation\Thumbnailer\Thumbnail',
		'via' => 'GET'
	)
);