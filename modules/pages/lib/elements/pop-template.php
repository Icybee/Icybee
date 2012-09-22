<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use Brickrouge\Element;

class PopTemplate extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('select', $attributes);
	}

	public function __toString()
	{
		global $core;

		$list = $core->site->templates;

		if (!$list)
		{
			return '<p class="warn">There is no template available.</p>';
		}

		$options = array_combine($list, $list);

		$this[self::OPTIONS] = array(null => '<auto>') + $options;

		return parent::__toString();
	}
}