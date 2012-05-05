<?php

namespace ICanBoogie;

require '../startup.php';

$core->session;

$request = HTTP\Request::from
(
	array
	(
		'path' => '/admin',
		'is_xhr' => true,
		'request_params' => array
		(
			Operation::DESTINATION => 'system.modules',
			Operation::NAME => isset($core->modules['articles']) ? 'deactivate' : 'activate',
			Operation::KEY => array
			(
				'articles' => 'on'
			)
		)
	)
);

$response = $request->post();

var_dump($response, Debug::fetch_messages('debug'));