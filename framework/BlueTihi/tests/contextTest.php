<?php

// sanity and error checking omitted for brevity
// note: it's a good idea to implement arrayaccess + countable + an
// iterator interface (like iteratoraggregate) as a triplet

class RecursiveArrayAccess extends ArrayObject /*implements ArrayAccess*/ {

    private $data = array();

    public $madonna = 'madonna';

    /*
    // necessary for deep copies
    public function __clone() {
        foreach ($this->data as $key => $value) if ($value instanceof self) $this[$key] = clone $value;
    }
    */

    /*
    public function __construct(array $data = array()) {
        foreach ($data as $key => $value) $this[$key] = $value;
    }
    */

    public function offsetSet($offset, $value)
    {
    	if (is_array($value))
    	{
    		$value = new static($value);
    	}

    	parent::offsetSet($offset, $value);
    }

    public function getArrayCopy()
    {
    	$array = parent::getArrayCopy();

    	foreach ($array as $key => $value)
    	{
    		if (is_object($value) && $value instanceof self)
    		{
    			$array[$key] = $value->getArrayCopy();
    		}
    	}

    	return $array;
    }

    /*
    public function toArray() {
        $data = $this->data;
        foreach ($data as $key => $value) if ($value instanceof self) $data[$key] = $value->toArray();
        return $data;
    }
    */

    /*
    // as normal
    public function offsetGet($offset) { return $this->data[$offset]; }
    public function offsetExists($offset) { return isset($this->data[$offset]); }
    public function offsetUnset($offset) { unset($this->data); }
    */
}

$a = new RecursiveArrayAccess();
$a[0] = array(1=>"foo", 2=>array(3=>"bar", 4=>array(5=>"bz")));
// oops. typo
$a[0][2][4][5] = "baz";

var_dump($a);

//var_dump($a);
//var_dump($a->toArray());

// isset and unset work too
//var_dump(isset($a[0][2][4][5])); // equivalent to $a[0][2][4]->offsetExists(5)
//unset($a[0][2][4][5]); // equivalent to $a[0][2][4]->offsetUnset(5);

// if __clone wasn't implemented then cloning would produce a shallow copy, and
$b = clone $a;
$b[0][2][4][5] = "xyzzy";
// would affect $a's data too
//echo $a[0][2][4][5]; // still "baz"

var_dump($b, $b->madonna, $b->getArrayCopy());

print_r($b->getArrayCopy());