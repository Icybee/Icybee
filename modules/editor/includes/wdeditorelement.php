<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;

class WdEditorElement extends Element
{
	const T_CONFIG = '#editor-config';
	const T_STYLESHEETS = '#editor-stylesheets';

	static public function to_content(array $params, $content_id, $page_id)
	{
		return isset($params['contents']) ? $params['contents'] : null;
	}

	static public function render($contents)
	{
		return $contents;
	}
}