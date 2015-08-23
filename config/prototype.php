<?php

namespace Icybee;

use ICanBoogie\Core;

$hooks = Hooks::class . '::';

return [

	Core::class . '::get_language' => $hooks . 'get_language',
	Core::class . '::set_language' => $hooks . 'set_language',
	Core::class . '::lazy_get_document' => 'Icybee\Document::get'

];
