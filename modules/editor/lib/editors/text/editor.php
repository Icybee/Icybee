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

/**
 * "Text" editor.
 */
class TextEditor implements Editor
{
	/**
	 * Returns content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns serialized content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}
	/**
	 * @return TextEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new TextEditorElement($attributes);
	}

	/**
	 * Returns content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		return $content;
	}
}