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

/**
 * "Textmark" editor.
 */
class TextmarkEditor implements Editor
{
	/**
	 * Returns content as is.
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns serialized content as is.
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}
	/**
	 * @return TextmarkEditorElement
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::create_element()
	 */
	public function create_element(array $attributes)
	{
		return new TextmarkEditorElement($attributes);
	}

	/**
	 * Renders content using the Textmark engine.
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		return \Textmark_Parser::parse($content);
	}
}