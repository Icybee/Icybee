<?php

use ICanBoogie\Object;

require '../../includes/startup.php';

//require_once '../wdelements/wddocument.php';

class foo extends Object
{
	private $a;

	public function __construct()
	{
		unset($a);
	}
}

$o = new foo();

$o->a = 12;
$o->b = 13;

$reflection = new ReflectionClass('foo');

var_dump($o, $reflection, $reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED));