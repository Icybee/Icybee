<?php

namespace Icybee\Modules\Dashboard;

return array
(
	'admin:dashboard' => array
	(
		'pattern' => '/admin/dashboard',
		'block' => 'dashboard',
		'controller' => __NAMESPACE__ . '\BlockController'
	)
);