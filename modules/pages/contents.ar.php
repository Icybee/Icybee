<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Pages;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;

class Content extends ActiveRecord
{
	/**
	 * The identifier of the page the content belongs to.
	 *
	 * @var int
	 */
	public $pageid;

	/**
	 * The identifier of the content.
	 *
	 * @var string
	 */
	public $contentid;

	/**
	 * The content.
	 *
	 * @var string
	 */
	public $content;

	/**
	 * The editor name used to edit and render the content.
	 *
	 * @var string
	 */
	public $editor;

	/**
	 * The rendered version of the content.
	 *
	 * @var string|object
	 */
	private $rendered;

	/**
	 * Returns the rendered contents.
	 *
	 * @return mixed
	 */
	protected function volatile_get_rendered()
	{
		return $this->render();
	}

	/**
	 * Renders the content as a string or an object.
	 *
	 * Exceptions thrown during the rendering are caught. The message of the exception is used
	 * as rendered content and the exception is rethrown.
	 *
	 * @throws Exception
	 *
	 * @return string|object The rendered content.
	 */
	public function render()
	{
		if ($this->rendered !== null)
		{
			return $this->rendered;
		}

		try
		{
			$editor = Core::get()->editors[$this->editor];
			$rendered = $editor->render($editor->unserialize($this->content));
		}
		catch (\ICanBoogie\Exception\HTTP $e)
		{
			$rendered = $e->getMessage();
		}
		catch (\Exception $e)
		{
			$rendered = \ICanBoogie\Debug::format_alert($e);
		}

		$this->rendered = $rendered;

		return $rendered;
	}

	public function __toString()
	{
		try
		{
			$rc = (string) $this->render();
		}
		catch (\Exception $e)
		{
			return \ICanBoogie\Debug::format_alert($e);
		}

		return $rc;
	}
}