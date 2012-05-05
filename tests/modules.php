<?php

require_once '../startup.php';

var_dump(isset($core->modules['nodes']));
var_dump(empty($core->modules['nodes']));
var_dump(isset($core->modules['__dummy__']));
var_dump(empty($core->modules['__dummy__']));

$a = $core->modules['nodes'];

var_dump($core->modules);
var_dump(array_keys($core->modules));