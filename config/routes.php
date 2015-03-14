<?php

use ICanBoogie\HTTP\Request;

return [

	'admin:index' => [

		'pattern' => '/admin',
		'controller' => 'Icybee\AdminIndexController'

	],

	'api:widget' => [

		'pattern' => '/api/widgets/:class',
		'controller' => 'Icybee\Operation\Widget\Get',
		'via' => Request::METHOD_GET

	],

	'api:widget/mode' => [

		'pattern' => '/api/widgets/:class/:mode',
		'controller' => 'Icybee\Operation\Widget\Get'

	],

	'api:activerecord/lock' => [

		'pattern' => '/api/:module/:key/lock',
		'controller' => 'Icybee\Operation\ActiveRecord\Lock',
		'via' => Request::METHOD_PUT

	],

	'api:activerecord/unlock' => [

		'pattern' => '/api/:module/:key/lock',
		'controller' => 'Icybee\Operation\ActiveRecord\Unlock',
		'via' => Request::METHOD_DELETE

	],

	'api:module/block' => [

		'pattern' => '/api/:module/blocks/:name',
		'controller' => 'Icybee\Operation\Module\Blocks',
		'via' => Request::METHOD_GET

	],

	'api:module/query-operation' => [

		'pattern' => '/api/query-operation/:module/:operation',
		'controller' => 'Icybee\Hooks::dispatch_query_operation'

	]
];
