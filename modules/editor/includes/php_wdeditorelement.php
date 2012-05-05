<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class php_WdEditorElement extends raw_WdEditorElement
{
	static public function render($contents)
	{
		global $core, $publisher;

		ob_start();

		eval('?>' . $contents);

		return ob_get_clean();
	}

	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				'class' => 'editor code php'
			)
		);

		global $document;

		$document->css->add('../public/code.css');
	}
}