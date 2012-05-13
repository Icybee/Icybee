<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

use Brickrouge\Element;

abstract class Widget extends Element
{
	const CONSTRUCTOR = '#widget-constructor';

	/**
	 * Interpolates a widget constructor name from the widget class.
	 *
	 * @param string $type
	 * @param array $attributes
	 */
	public function __construct($type, array $attributes=array())
	{
		$class = get_class($this);
		$constructor = basename(strtr($class, '\\', '/'));

		parent::__construct
		(
			$type, $attributes + array
			(
				self::CONSTRUCTOR => $constructor
			)
		);
	}

	protected function render_class(array $class_names)
	{
		$class = 'widget-' . \ICanBoogie\hyphenate($this[self::CONSTRUCTOR]);
		$class_names[$class] = $class;

		return parent::render_class($class_names);
	}

	protected function render_dataset(array $dataset)
	{
		$dataset['widget-constructor'] = $this[self::CONSTRUCTOR];

		return parent::render_dataset($dataset);
	}

	public function get_results(array $options=array())
	{
		throw new \Exception('The widget class %class does not implement results', array('%class' => get_class($this)));
	}
}