<?php

return array
(
	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_site' => 'ICanBoogie\Hooks\Sites::__get_node_site',
		'ICanBoogie\Core::__get_site' => 'ICanBoogie\Hooks\Sites::__get_core_site',
		'ICanBoogie\Core::__get_site_id' => 'ICanBoogie\Hooks\Sites::__get_core_site_id'
	)
);