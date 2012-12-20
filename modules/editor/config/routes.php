<?php

namespace Icybee\Modules\Editor;

use ICanBoogie\HTTP\Request;

return array
(
	'api:editors/new-pane' => array
	(
		'pattern' => '/api/editors/tabbable/new-pane',
		'controller' => __NAMESPACE__ . '\TabbableNewPaneOperation',
		'via' => Request::METHOD_GET
	)
);