<?php

return array
(
	'autoload' => array
	(
		'BrickRouge\Widget\Users\Login' => $path . 'widgets' . DIRECTORY_SEPARATOR . 'login.php',
		'BrickRouge\Widget\Users\NonceRequest' => $path . 'widgets' . DIRECTORY_SEPARATOR . 'nonce-request.php'
	),

	'config constructors' => array
	(
		'user' => array('merge', 'user')
	)
);