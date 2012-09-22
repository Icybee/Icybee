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

use ICanBoogie\Exception;

/**
 * "Widgets" editor.
 */
class WidgetsEditor implements Editor
{
	/**
	 * Returns a JSON string.
	 *
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return json_encode(array_keys($content));
	}

	/**
	 * Returns unserialized JSON content.
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return (array) json_decode($serialized_content);
	}
	/**
	 * @return WidgetsEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new WidgetsEditorElement($attributes);
	}

	/**
	 * Renders selected widgets.
	 *
	 * @see Icybee\Modules\Editor.Editor::render()
	 */
	public function render($content)
	{
		global $core;

		if (!$content)
		{
			return;
		}

		$availables = $core->configs->synthesize('widgets', 'merge');

		if (!$availables)
		{
			return;
		}

		$selected = array_flip($content);
		$undefined = array_diff_key($selected, $availables);

		if ($undefined)
		{
			throw new Exception('Undefined widget(s): :list', array(':list' => implode(', ', array_keys($undefined))));
		}

		$list = array_intersect_key($availables, $selected);

		if (!$list)
		{
			return;
		}

		$html = '';
		$list = array_merge($selected, $list);

		foreach ($list as $id => $widget)
		{
			$html .= '<div id="widget-' . \ICanBoogie\normalize($id) . '" class="widget">' . $this->render_widget($widget, $id) . '</div>';
		}

		return $html;
	}

	private function render_widget($widget, $id)
	{
		global $core;

		if (isset($widget['file']))
		{
			$file = $widget['file'];

			if (substr($file, -4, 4) == '.php')
			{
				ob_start();

				require $file;

				return ob_get_clean();
			}
			else if (substr($file, -5, 5) == '.html')
			{
				$patron = new \Patron\Engine;

				return $patron(file_get_contents($file), null, array('file' => $file));
			}
			else
			{
				throw new Exception('Unable to process file %file, unsupported type', array('%file' => $file));
			}
		}
		else if (isset($widget['module']) && isset($widget['block']))
		{
			return $core->modules[$widget['module']]->getBlock($widget['block']);
		}
		else
		{
			throw new Exception('Unable to render widget %widget, its description is invalid.', array('%widget' => $id));
		}
	}
}