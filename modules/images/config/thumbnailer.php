<?php

return array
(
	'$icon' => array
	(
		array
		(
			'w' => 24,
			'h' => 24,
			'format' => 'png'
		),

		'module' => 'images',
		'title' => 'Icône'
	),

	'$popup' => array
	(
		array
		(
			'w' => 200,
			'h' => 200,
			'method' => 'surface',
			'no-upscale' => true,
			'quality' => 90
		),

		'module' => 'images',
		'title' => 'Popup'
	),

	'$gallery' => array
	(
		array
		(
			'w' => 128,
			'h' => 128,
			'method' => 'scale-min',
			'quality' => 90
		),

		'module' => 'images',
		'title' => 'Gallery'
	)
);