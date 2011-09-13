<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class textmark_WdEditorElement extends WdEditorElement
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			'textarea', $tags + array
			(
				'class' => 'editor textmark'
			)
		);
	}

	static public function render($contents)
	{
		return Textmark_Parser::parse($contents);
	}
}