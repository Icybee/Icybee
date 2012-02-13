<?php

return array
(
	'thumbnail' => array
	(
		'pattern' => '/api/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:w/h/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:w/h' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:w/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:w' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:h/m' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	'thumbnail:h' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => 'get'
	),

	/*
	 * Module's thumbnails
	 */

	'thumbnail:image' => array
	(
		'pattern' => '/api/:module/:nid/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:w/h/m' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:w/h' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:w/m' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:w' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:h/m' => array
	(
		'pattern' => '/api/:module/:nid/x<h:\d+>/:method',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => 'get'
	),

	'thumbnail:image:h' => array
	(
		'pattern' => '/api/:module/:nid/x<h:\d+>',
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