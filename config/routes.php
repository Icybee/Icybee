<?php

namespace Icybee;

use ICanBoogie\HTTP\Request;

return [
/*
	'admin:index' => [

		'pattern' => '/admin',
		'controller' => Routing\AdminIndexController::class

	],
*/
	'api:widget' => [

		'pattern' => '/api/widgets/:class',
		'controller' => Operation\Widget\Get::class,
		'via' => Request::METHOD_GET

	],

	'api:widget/mode' => [

		'pattern' => '/api/widgets/:class/:mode',
		'controller' => Operation\Widget\Get::class

	],

	'api:activerecord/lock' => [

		'pattern' => '/api/:module/:key/lock',
		'controller' => Operation\ActiveRecord\Lock::class,
		'via' => Request::METHOD_PUT

	],

	'api:activerecord/unlock' => [

		'pattern' => '/api/:module/:key/lock',
		'controller' => Operation\ActiveRecord\Unlock::class,
		'via' => Request::METHOD_DELETE

	],

	'api:module/block' => [

		'pattern' => '/api/:module/blocks/:name',
		'controller' => Operation\Module\Blocks::class,
		'via' => Request::METHOD_GET

	],

	'api:module/query-operation' => [

		'pattern' => '/api/query-operation/:module/:operation',
		'controller' => Hooks::class . '::dispatch_query_operation'

	]
];
