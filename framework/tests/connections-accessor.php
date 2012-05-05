<?php

require_once '../../includes/startup.php';

$primary = $core->connections['primary'];

$primary->optimize();

//var_dump($primary);

$local = $core->connections['local'];

$local->optimize();

//var_dump($local);

foreach ($core->connections as $id => $value)
{
	var_dump($id, $value);
}