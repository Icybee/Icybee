<?php

require '../../includes/startup.php';

$primary = $core->connections['primary'];
$local = $core->connections['local'];

var_dump($primary, $local);

var_dump(isset($primary['nodes'])); // true
var_dump(isset($primary['nodes_blha'])); // false

var_dump(isset($local['system_registry'])); // true
var_dump(isset($local['system_registry_blha'])); // false