<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

/**
 * Event class for the event `alter_css_class_names`.
 */
class AlterCSSClassNamesEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the class names to alter.
	 *
	 * @var array[string]mixed
	 */
	public $names;

	/**
	 * The event is constructed with the type `alter_css_class_names`.
	 *
	 * @param $target
	 * @param array $payload
	 */
	public function __construct($target, array &$names)
	{
		$this->names = &$names;

		parent::__construct($target, 'alter_css_class_names');
	}
}

/**
 * Interface for classes implementing CSSClassNames.
 *
 * @property-read string $css_class The CSS class of the instance.
 * @property-read array[string]mixed $css_class_names The CSS class names of the instance.
 */
interface CSSClassNames
{
	public function css_class($modifiers=null);
}