<?php

return array
(
	'icybee:widget' => array
	(
		'pattern' => '/api/widgets/:class',
		'class' => 'Icybee\Operation\Widget\Get',
		'via' => 'get'
	),

	'icybee:widget:mode' => array
	(
		'pattern' => '/api/widgets/:class/:mode',
		'class' => 'Icybee\Operation\Widget\Get'
	),

	'icybee:activerecord:lock' => array
	(
		'pattern' => '/api/:module/:key/lock',
		'class' => 'Icybee\Operation\ActiveRecord\Lock',
		'via' => 'put'
	),

	'icybee:activerecord:unlock' => array
	(
		'pattern' => '/api/:module/:key/lock',
		'class' => 'Icybee\Operation\ActiveRecord\Unlock',
		'via' => 'delete'
	),

	'icybee:module:block' => array
	(
		'pattern' => '/api/:module/blocks/:name',
		'class' => 'Icybee\Operation\Module\Blocks',
		'via' => 'get'
	),

	'icybee:module:query-operation' => array
	(
		'pattern' => '/api/query-operation/:module/:operation',
		'callback' => 'Icybee\Hooks::dispatch_query_operation'
	)
);