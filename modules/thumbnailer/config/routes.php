<?php

use ICanBoogie\HTTP\Request;

return array
(
	'thumbnail' => array
	(
		'pattern' => '/api/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:w/h/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:w/h' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:w/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:w' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:h/m' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:h' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	/*
	 * Module's thumbnails
	 */

	'thumbnail:image' => array
	(
		'pattern' => '/api/:module/:nid/thumbnail',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:w/h/m' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>x<h:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:w/h' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:w/m' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>/:m',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:w' => array
	(
		'pattern' => '/api/:module/:nid/<w:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:h/m' => array
	(
		'pattern' => '/api/:module/:nid/x<h:\d+>/:method',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image:h' => array
	(
		'pattern' => '/api/:module/:nid/x<h:\d+>',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'thumbnail:image/version' => array
	(
		'pattern' => '/api/:module/:nid/thumbnails/:version',
		'class' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	)
);