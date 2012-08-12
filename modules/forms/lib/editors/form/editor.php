<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Forms;

use ICanBoogie\Core;

/**
 * "Form" editor.
 */
class FormEditor implements \ICanBoogie\Modules\Editor\Editor
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
	 * @return FormEditorElement
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::create_element()
	 */
	public function create_element(array $attributes)
	{
		return new FormEditorElement($attributes);
	}

	/**
	 * Returns a _Form_ active record.
	 *
	 * @return ICanBoogie\ActiveRecord\Form
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		return $content ? Core::get()->models['forms'][$content] : null;
	}
}