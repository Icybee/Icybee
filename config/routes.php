<?php

use ICanBoogie\HTTP\Request;

return array
(
	'admin:index' => array
	(
		'pattern' => '/admin',
		'controller' => 'Icybee\AdminIndexController'
	),

	'api:widget' => array
	(
		'pattern' => '/api/widgets/:class',
		'class' => 'Icybee\Operation\Widget\Get',
		'via' => Request::METHOD_GET
	),

	'api:widget/mode' => array
	(
		'pattern' => '/api/widgets/:class/:mode',
		'class' => 'Icybee\Operation\Widget\Get'
	),

	'api:activerecord/lock' => array
	(
		'pattern' => '/api/:module/:key/lock',
		'class' => 'Icybee\Operation\ActiveRecord\Lock',
		'via' => Request::METHOD_PUT
	),

	'api:activerecord/unlock' => array
	(
		'pattern' => '/api/:module/:key/lock',
		'class' => 'Icybee\Operation\ActiveRecord\Unlock',
		'via' => Request::METHOD_DELETE
	),

	'api:module/block' => array
	(
		'pattern' => '/api/:module/blocks/:name',
		'class' => 'Icybee\Operation\Module\Blocks',
		'via' => Request::METHOD_GET
	),

	'api:module/query-operation' => array
	(
		'pattern' => '/api/query-operation/:module/:operation',
		'callback' => 'Icybee\Hooks::dispatch_query_operation'
	)
);