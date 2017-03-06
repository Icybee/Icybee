<?php

namespace Icybee;

use ICanBoogie\Application;

$hooks = Hooks::class . '::';

return [

	Application::class . '::get_language' => $hooks . 'get_language',
	Application::class . '::set_language' => $hooks . 'set_language',
	Application::class . '::lazy_get_document' => 'Icybee\Element\Document::get'

];
