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
