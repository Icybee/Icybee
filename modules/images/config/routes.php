<?php

return array
(
	':admin/manage' => array
	(

	),

	':admin/gallery' => array
	(
		'pattern' => '/admin/images/gallery',
		'title' => '.gallery',
		'block' => 'gallery',
		'workspace' => 'resources'
	),

	'redirect:/admin/resources' => array
	(
		'pattern' => '/admin/resources',
		'location' => '/admin/images'
	)
);