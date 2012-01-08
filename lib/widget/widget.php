<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

use BrickRouge\Element;
use ICanBoogie\Exception;

abstract class Widget extends Element
{
	/**
	 * Interpolates a css class from the widget class and add it to the class list.
	 *
	 * @param string $type
	 * @param array $tags
	 */
	public function __construct($type, $tags)
	{
		$class = get_class($this);

		if (strpos($class, 'BrickRouge\Widget') !== 0)
		{
			throw new Exception('The widget class must be in the <em>BrickRouge\Widget</em> namespace');
		}

		$class = substr($class, 18);
		$class = 'widget-' . wd_hyphenate($class);

		parent::__construct($type, $tags);

		$this->add_class($class);
	}

	public function get_results(array $options=array())
	{
		throw new Exception('The widget class %class does not implement results', array('%class' => get_class($this)));
	}
}