<?php

namespace ICanBoogie\Modules\Editor;

use ICanBoogie\HTTP\Request;

return array
(
	'api:editors/new-pane' => array
	(
		'pattern' => '/api/editors/tabbable/new-pane',
		'class' => __NAMESPACE__ . '\TabbableNewPaneOperation',
		'via' => Request::METHOD_GET
	)
);