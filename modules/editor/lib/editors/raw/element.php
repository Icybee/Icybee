<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use Brickrouge\Element;

/**
 * "Raw" editor element.
 */
class RawEditorElement extends Element implements EditorElement
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'textarea', $attributes + array
			(
				'class' => 'editor raw'
			)
		);
	}
}