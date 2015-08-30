<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\FormBlock;

use Icybee\Block\FormBlock;

/**
 * Event class for the `Icybee\FormBlock::alter_properties:before` event.
 */
class BeforeAlterValuesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_properties:before`.
	 *
	 * @param FormBlock $target
	 * @param array $payload
	 */
	public function __construct(FormBlock $target, array $payload)
	{
		parent::__construct($target, 'alter_values:before', $payload);
	}
}
