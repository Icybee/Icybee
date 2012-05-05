<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Patron;

class Template implements \IteratorAggregate
{
	public $file;
	public $nodes;

	public function getIterator()
	{
		return new \ArrayIterator($this->nodes);
	}
}
