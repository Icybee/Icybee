<?php

namespace BlueTihi;

class Context implements \ArrayAccess, \IteratorAggregate
{
	protected $values = array();
	protected $values_stack = array();
	protected $depth = 0;
	protected $this_arg;

	public function __construct(array $values, $this_arg=null)
	{
		$this->values = $values;
		$this->this_arg = $this_arg;
	}

	/*
	 * ArrayAccess
	 */

	public function offsetExists($offset)
	{
		return isset($this->values[$offset]);
	}

	/*
	public function offsetGet($offset)
	{
		#
		# workdaround for &offsetGet for PHP < 5.3.4
		#

		$v = &$this->values[$offset];

		return $v;
	}
	*/

	public function &offsetGet($offset)
	{
		return $this->values[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->values[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->values[$offset]);
	}

	/*
	 * /ArrayAccss
	 */

	public function getIterator()
	{
		return new \ArrayIterator($this->values);
	}

	public function push()
	{
		$this->depth++;
		array_push($this->values_stack, $this->values);
	}

	public function pop()
	{
		$this->depth--;
		$this->values = array_pop($this->values_stack);
	}

	public function keys()
	{
		return array_keys($this->values);
	}

	public function values()
	{
		return array_values($this->values);
	}
}