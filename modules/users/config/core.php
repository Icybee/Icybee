<?php

return array
(
	'autoload' => array
	(
		'Brickrouge\Widget\Users\Login' => $path . 'widgets' . DIRECTORY_SEPARATOR . 'login.php',
		'Brickrouge\Widget\Users\LoginCombo' => $path . 'widgets' . DIRECTORY_SEPARATOR . 'login-combo.php',
		'Brickrouge\Widget\Users\NonceRequest' => $path . 'widgets' . DIRECTORY_SEPARATOR . 'nonce-request.php'
	),

	'config constructors' => array
	(
		'user' => array('merge', 'user')
	)
);