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
 * "Tabbable" editor.
 */
class TabbableEditor implements Editor
{
	/**
	 * Serializes the content as a JSON string.
	 *
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		global $core;

		$panes = array();

		if ($content && is_array($content))
		{
			foreach ($content as $properties)
			{
				$editor_id = $properties['editor_id'];

				$panes[] = array
				(
					'title' => $properties['title'],
					'editor_id' => $editor_id,
					'serialized_content' => $core->editors[$editor_id]->serialize($properties['content'])
				);
			}
		}

		return json_encode($panes);
	}

	/**
	 * Unserialize the content as an array.
	 *
	 * @return array
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		if (is_array($serialized_content))
		{
			return $serialized_content;
		}

		return json_decode($serialized_content, true);
	}

	/**
	 * @return TabbableEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new TabbableEditorElement($attributes);
	}

	protected $renderer;

	/**
	 * The content is rendered using a {@link TabbableEditorRenderer} instance.
	 *
	 * @see Icybee\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		$renderer = $this->renderer;

		if (!$renderer)
		{
			$this->renderer = $renderer = new TabbableEditorRenderer($this);
		}

		return $renderer($content);
	}
}