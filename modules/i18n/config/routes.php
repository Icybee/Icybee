<?php

use ICanBoogie\HTTP\Request;

return array
(
	'i18n:languages' => array
	(
		'pattern' => '/api/components/i18n/nodes/<nid:\d+>/language',
		'callback' => 'WdI18nElement::operation_nodes_language',
		'via' => Request::METHOD_GET
	)
);