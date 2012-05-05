<?php

use ICanBoogie\Route;

require_once '../startup.php';

$a = Route::parse('/blog/<categoryslug:[^/]+>/<slug:[^\.]+>.html');

var_dump($a);

$a = Route::parse('/blog/:categoryslug/:slug.html');

var_dump($a);

$a = Route::parse('/api/images/372/thumbnail?w=600&method=fixed-width&quality=80');

var_dump($a);