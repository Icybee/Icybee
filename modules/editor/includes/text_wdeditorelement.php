<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class text_WdEditorElement extends WdEditorElement
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			Element::E_TEXT, $tags + array
			(
				'class' => 'editor text'
			)
		);
	}
}