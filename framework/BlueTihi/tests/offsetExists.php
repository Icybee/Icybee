<?php

class A implements ArrayAccess
{
	public function offsetExists($offset)
	{
		echo "testing offset '$offset' in A<br />";
	}

	public function offsetGet($offset) {}
	public function offsetSet($offset, $value) {}
	public function offsetUnset($offset) {}
}

class B extends ArrayObject
{
	public function offsetExists($offset)
	{
		echo "testing offset '$offset' in B<br />";
	}
}

$a = new A();

if (!isset($a['dummy']))
{
	echo "dummy is not set in A<br />";
}

if (!isset($a['dummy']['pouic']))
{
	echo "dummy.pouic is not set in A<br />";
}

$b = new B(array());

if (!isset($b['dummy']))
{
	echo "dummy is not set in B<br />";
}

if (!isset($b['dummy']['pouic']))
{
	echo "dummy.pouic is not set in B<br />";
}