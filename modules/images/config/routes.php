<?php

return array
(
	'admin:images/gallery' => array
	(
		'pattern' => '/admin/images/gallery',
		'controller' => 'Icybee\BlockController',
		'title' => '.gallery',
		'block' => 'gallery'
	),

	'!admin:config' => array
	(

	),

	'redirect:admin/resources' => array
	(
		'pattern' => '/admin/resources',
		'location' => '/admin/images'
	)
);