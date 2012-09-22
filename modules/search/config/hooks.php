<?php

namespace Icybee\Modules\Search;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'patron.markups' => array
	(
		'search:form:quick' => array
		(
			$hooks . 'markup_form', array
			(

			)
		)
	)
);