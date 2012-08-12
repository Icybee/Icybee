<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Editor;

use Brickrouge\Element;

/**
 * "Textmark" editor element.
 */
class TextmarkEditorElement extends Element implements EditorElement
{
	public function __construct(array $attributes)
	{
		parent::__construct
		(
			'textarea', $attributes + array
			(
				'class' => 'editor textmark'
			)
		);
	}
}