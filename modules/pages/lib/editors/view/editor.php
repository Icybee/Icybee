<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\Exception;

use Brickrouge\Element;

/**
 * View editor.
 */
class ViewEditor implements \ICanBoogie\Modules\Editor\Editor
{
	/**
	 * Returns the content as is.
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns the serialized content as is.
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}

	/**
	 * @return ViewEditorElement
	 *
	 * @see ICanBoogie\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new ViewEditorElement($attributes);
	}

	public function render($id, $engine=null, $template=null) // TODO-20120811: this is pretty bad, we should use the request context or something
	{
		global $core;

		$patron = \WdPatron::get_singleton();
		$page = isset($core->request->context->page) ? $core->request->context->page : null;

		if (!$page)
		{
			$page = $core->site->resolve_view_target($id);

			if (!$page)
			{
				$page = $core->site->home;
			}
		}

		$views = \Icybee\Views::get();

		if (empty($views[$id]))
		{
			throw new Exception('Unknown view: %id.', array('%id' => $id));
		}

		$definition = $views[$id];
		$class = $definition['class'] ?: 'Icybee\Views\View';
		$view = new $class($id, $definition, $patron, $core->document, $page);
		$rc = $view();

		if ($template)
		{
			return $engine($template, $rc);
		}

		return $rc;
	}
}