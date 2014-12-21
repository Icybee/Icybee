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

abstract class Widget extends Element
{
	/**
	 * Interpolates a widget constructor name from the widget class.
	 *
	 * @param string $type
	 * @param array $attributes
	 */
	public function __construct($type, array $attributes=[])
	{
		$class = get_class($this);
		$constructor = basename(strtr($class, '\\', '/'));

		parent::__construct($type, $attributes + [

			Element::IS => $constructor

		]);
	}

	protected function render_class(array $class_names)
	{
		$class = 'widget-' . \ICanBoogie\hyphenate($this[Element::IS]);
		$class_names[$class] = $class;

		return parent::render_class($class_names);
	}

	/**
	 * @param array $options
	 *
	 * @throws \Exception
	 */
	public function get_results(array $options=[])
	{
		throw new \Exception('The widget class %class does not implement results', [ '%class' => get_class($this) ]);
	}
}
