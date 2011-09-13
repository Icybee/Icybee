<?php

return array
(
	'/api/widgets/:class' => array
	(
		'class' => 'Icybee\Operation\Widget\Get'
	),

	'/api/widgets/:class/:mode' => array
	(
		'class' => 'Icybee\Operation\Widget\Get'
	),

	'/api/:module/:key/lock' => array
	(
		'class' => 'Icybee\Operation\ActiveRecord\Lock'
	),

	'/api/:module/:key/unlock' => array
	(
		'class' => 'Icybee\Operation\ActiveRecord\Unlock'
	),

	'/api/:module/blocks/:name' => array
	(
		'class' => 'Icybee\Operation\Module\Blocks'
	),

	'/api/query-operation/:module/:operation' => array
	(
		'callback' => 'Icybee\Hooks::dispatch_query_operation'
	)
);