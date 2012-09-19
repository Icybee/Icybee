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
 * "Image" editor.
 */
class ImageEditor implements Editor
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
	 * @return ImageEditorElement
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new ImageEditorElement($attributes);
	}

	/**
	 * Returns image active record.
	 *
	 * @return ICanBoogie\ActiveRecord\Image
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		global $core;

		return $content ? $core->models['images'][$content] : null;
	}
}